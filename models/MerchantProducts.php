<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "merchant_products".
 *
 * @property int $id
 * @property int $created_at timestamp-метка создания записи (в формате UNIX-time)
 * @property string|null $vendor_code артикул товара
 * @property string $title название товара
 * @property int|null $price актуальная цена
 * @property int|null $old_price старая цена
 * @property string|null $image Изображение товара
 * @property int|null $quantity Количество товара
 */
class MerchantProducts extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'merchant_products';
    }

    /**
     * @return array|array[]
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'title'], 'required'],
            [['created_at', 'price', 'old_price', 'quantity'], 'integer'],
            [['vendor_code', 'image'], 'string', 'max' => 255],
            [['title'], 'string', 'max' => 300],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'timestamp-метка создания записи (в формате UNIX-time)',
            'vendor_code' => 'артикул товара',
            'title' => 'название товара',
            'price' => 'актуальная цена',
            'old_price' => 'старая цена',
            'image' => 'Изображение товара',
            'quantity' => 'Количество товара',
        ];
    }
}
