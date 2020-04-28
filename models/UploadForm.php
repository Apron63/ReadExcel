<?php

namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

/**
 * Форма загрузки файла с данными.
 */
class UploadForm extends Model
{
    /** @var UploadedFile $fileName */
    public $fileName;
    /** @var bool $uploadSuccess */
    public $uploadSuccess;
    /** @var string $encoding Родировка файла для CSV формата */
    public $encoding = '';
    /** @var string $separator Разделитель для CSV */
    public $separator = '';


    /**
     * Валидаторы
     * @return array
     */
    public function rules()
    {
        return [
            [['fileName'],
                'file',
                'skipOnEmpty' => false,
                'checkExtensionByMimeType' => false,
                'extensions' => 'csv, xls, xlsx',
                'wrongExtension' => 'Допускаются только {extensions} расширения у файлов'],
            [['uploadSuccess'], 'boolean'],
            [['encoding', 'separator'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'fileName' => 'Имя файла для загрузки',
            'encoding' => 'Кодировка',
            'separator' => 'Разделитель',
        ];
    }

    /**
     * Загрузка файла на сервер
     * @return bool
     */
    public function upload()
    {
        if ($this->validate()) {
            $this->fileName->saveAs('uploads/' . $this->fileName->baseName . '.' . $this->fileName->extension);
            $this->uploadSuccess = true;
            return true;
        } else {
            return false;
        }
    }
}
