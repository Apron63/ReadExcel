<?php

namespace app\controllers;

use app\models\MerchantProducts;
use app\service\ExcelProviderService;
use Yii;
use yii\db\Exception;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UploadedFile;

use app\models\UploadForm;

class SiteController extends Controller
{
    /** @var array $columnsName Наименования столбцов для таблицы */
    private $columnsName;
    /** @var array $dataProvider Данные считанные из таблицы */
    private $dataProvider;
    /** @var string $fileName Имя файла с данными */
    private $fileName;

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Загрузка файла.
     * @return string
     */
    public function actionIndex()
    {
        $model = new UploadForm();

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $model->fileName = UploadedFile::getInstance($model, 'fileName');
            if ($model->upload()) {
                return $this->redirect([
                    'site/select-fields',
                    'fileName' => $model->fileName->name,
                    'encoding' => $model->encoding,
                    'separator' => $model->separator
                ]);
            }
        }

        return $this->render('index', ['model' => $model]);
    }

    /**
     * Выбор колонок для импорта данных.
     * @param $fileName
     * @param null $encoding
     * @param null $separator
     * @return string
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function actionSelectFields($fileName, $encoding = null, $separator = null)
    {
        if (!$this->getExcelData($fileName, $encoding, $separator)) {
            Yii::$app->session->setFlash('error', 'Невозможно загрузить данные');
            return $this->redirect(['index']);
        }
        return $this->render('select-fields', [
            'fileName' => $fileName,
            'columnsName' => $this->columnsName,
            'dataProvider' => $this->dataProvider,
            'encoding' => $encoding,
            'separator' => $separator
        ]);
    }

    /**
     * Сохранить Excel таблицу в базу данных на основе выбранных столбцов.
     * @param string $fileName
     * @param string $params
     * @param string|null $encoding
     * @param string|null $separator
     * @return Response
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function actionSaveToTable($fileName, $params, $encoding = null, $separator = null)
    {
        if (!$this->getExcelData($fileName, $encoding, $separator)) {
            Yii::$app->session->setFlash('error', 'Невозможно загрузить данные');
            return $this->redirect(['index']);
        }

        $importConfig = json_decode($params, true);

        $result = $this->saveDataToDbTable($importConfig);

        if ($result > 0) {
            Yii::$app->session->setFlash('success', 'Данные успешно сохранены!');
        } else {
            Yii::$app->session->setFlash('error', 'Данные не были добавлены');
        }

        return $this->redirect(['index']);
    }

    /**
     * Считывает данные из Excel файла в массив данных.
     * @param $fileName
     * @param null $encoding
     * @param null $separator
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function getExcelData($fileName, $encoding = null, $separator = null)
    {
        // Сервис для обраоботки данных.
        $excelProviderService = new ExcelProviderService($fileName, $encoding, $separator);
        // Загружаем данные.
        $this->dataProvider = $excelProviderService->getExcelTableData();
        if (!$this->dataProvider) {
            return false;
        }
        // А также получаем массив с колонками, которые необходимо выбрать.
        $this->columnsName = $excelProviderService->getColumnsData();
        if (!$this->columnsName) {
            return false;
        }
        return true;
    }

    /**
     * Сохранение данных в БД.
     * Этот метод использует низкоуровневую пакетную загрузку для оптимизации.
     * @param array $importConfig
     * @throws Exception
     * @return int Число обработанных строк
     */
    private function saveDataToDbTable($importConfig)
    {
        // Получаем подключение к БД.
        $connection = Yii::$app->db;

        // Строим массив столбцов для таблицы БД.
        $columns = [];
        foreach ($importConfig as $element) {
            $columns[] = $element['value'];
        }

        // Добаивим колонку created_at так как не используется высокоуровневый доступ и TimestampBehavior не работает.
        $columns[] = 'created_at';

        // Значение для created_at чтобы не вызывать функцию многократно.
        $createdAtValue = time();

        // Заполняем данные.
        $data = [];
        foreach ($this->dataProvider as $row) {
            $fileDataRow = [];
            foreach($importConfig as $cell) {
                $fileDataRow[] = $row[$cell['data']];
            }
            $fileDataRow[] = $createdAtValue;
            $data[] = $fileDataRow;
        }

        // Сохраняем данные в таблицу.
        return $connection->createCommand()->batchInsert(
            MerchantProducts::tableName(),
            $columns,
            $data
        )->execute();
    }
}
