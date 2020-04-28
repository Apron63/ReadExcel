<?php

use yii\db\Migration;

/**
 * Создаем таблицу в БД `{{%merchant_products}}`.
 */
class m200425_073025_create_merchant_products_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%merchant_products}}', [
            'id' => $this->primaryKey()->notNull(),
            'created_at' => $this->integer(11)
                ->notNull()
                ->comment('timestamp-метка создания записи (в формате UNIX-time)'),
            'vendor_code' => $this->string(255)->defaultValue(null)->comment('артикул товара'),
            'title' => $this->string(300)->notNull()->comment('название товара'),
            'price' => $this->integer(11)->defaultValue(null)->comment('актуальная цена'),
            'old_price' => $this->integer(11)->defaultValue(null)->comment('старая цена'),
            'image' => $this->string(255)->defaultValue(null)->comment('Изображение товара'),
            'quantity' => $this->integer(11)->defaultValue(null)->comment('Количество товара'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%merchant_products}}');
    }
}
