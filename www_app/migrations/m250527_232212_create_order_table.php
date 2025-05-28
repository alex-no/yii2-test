<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%order}}`.
 */
class m250527_232212_create_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%order}}', [
            'id' => $this->primaryKey(),

            'user_id' => $this->bigInteger()->unsigned()->notNull()->comment('User who created the order'),
            'order_id' => $this->string(64)->notNull()->unique()->comment('Public order ID for external use'),
            'amount' => $this->decimal(10, 2)->notNull()->comment('Payment amount'),
            'currency' => $this->string(3)->notNull()->defaultValue('UAH')->comment('Currency code'),
            'payment_status' => "ENUM('pending', 'success', 'fail', 'cancel', 'refund', 'expired') NOT NULL DEFAULT 'pending' COMMENT 'Payment status'",
            'description' => $this->string(255)->null()->comment('Order description'),

            'paid_at' => $this->timestamp()->defaultValue(null)->comment('Payment timestamp'),
            'created_at' => $this->timestamp()->defaultValue(null)->comment('Creation timestamp'),
            'updated_at' => $this->timestamp()->defaultValue(null)->comment('Last update timestamp'),
        ]);

        $this->createIndex('idx-order-user_id', '{{%order}}', 'user_id');
        $this->createIndex('idx-order-payment_status', '{{%order}}', 'payment_status');

        $this->addForeignKey(
            'fk-order-user_id',
            '{{%order}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',   // on delete
            'RESTRICT'   // on update
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-order-user_id', '{{%order}}');
        $this->dropTable('{{%order}}');
    }
}
