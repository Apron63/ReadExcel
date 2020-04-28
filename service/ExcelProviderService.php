<?php

namespace app\service;

use Yii;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\BaseReader;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Reader\Csv;


class ExcelProviderService
{
    /** @var string|null $fileName Имя файла с таблицей */
    private $fileName;

    /** @var string[] $columnsName Наименование столбцов для импорта */
    private $columnsName;

    /** @var string|null $encoding Кодировка файла */
    private $encoding;

    /** @var string|null $separator Разделитель для CSV */
    private $separator;

    /**
     * Конструктор класса
     * @param string|null $fileName
     * @param null $encoding
     * @param null $separator
     */
    public function __construct($fileName = null, $encoding = null, $separator = null)
    {
        $this->columnsName = [
            'vendor_code' => 'Артикул',
            'title' => 'Название',
            'price' => 'Цена',
            'old_price' => 'Старая цена',
            'image' => 'Изображение',
            'quantity' => 'Количество',
        ];
        $this->fileName = $fileName;
        $this->encoding = $encoding;
        $this->separator = $separator;
    }

    /**
     * Загружаем данные из Excel таблицы.
     * @return array|null
     * @throws Exception
     */
    public function getExcelTableData()
    {
        // Если имя файла не задано, досрочный выход.
        if (!$this->fileName) {
            return null;
        }

        // Абсолютное имя файла.
        $fullFileName = Yii::getAlias('@webroot') . '/uploads/' . $this->fileName;
        // Загружаем данные из файла.
        $data = [];

        /** @var BaseReader $reader Загрузчик файла */
        $reader = IOFactory::createReaderForFile($fullFileName);
        // Значительно увеличивается скорость обработки так как только читаем.
        $reader->setReadDataOnly(true);
        // Для файлов CSV допускается задавать кодировку и разделитель.
        if ($reader instanceof Csv) {
            if (!empty($this->encoding)) {
                $reader->setInputEncoding($this->encoding);
            }
            if (!empty($this->separator)) {
                $reader->setDelimiter($this->separator);
            }
        }
        // Для предотвращения исключения в случае ошибок с кодировкой.
        try {
            // Загружаем Excel лист.
            $spreadsheet = $reader->load($fullFileName);
        } catch (\Exception $exception) {
            return null;
        }

        // Определяем параметры файла с данными.
        $worksheet = $spreadsheet->getSheet(0);
        $lastRow = $worksheet->getHighestRow();
        $lastColumn = $worksheet->getHighestColumn();

        // Читаем данные из листа.
        for ($item = 1; $item <= $lastRow; $item++) {
            $row = [];
            for ($column = 'A'; $column <= $lastColumn; $column++) {
                $value = $worksheet->getCell($column . $item)->getValue();
                $row[$column] = $value;
            }
            $data[] = $row;
        }

        // Если элементов не найдено.
        if (empty($data)) {
            return null;
        }

        return $data;
    }

    /**
     * Выдает данные о столбца, которые необходимы для заполнения таблицы.
     * @return array|null
     */
    public function getColumnsData()
    {
        return $this->columnsName;
    }

    public function saveDataToDbTable($config, $data)
    {

    }
}