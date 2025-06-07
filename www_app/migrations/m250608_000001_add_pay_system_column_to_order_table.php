<?php

use yii\db\Migration;

/**
 * Handles adding column `pay_system` to table `order`.
 */
class m250608_000001_add_pay_system_column_to_order_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn(
            '{{%order}}',
            'pay_system',
            $this->string(32)->after('currency')
        );
    }

    public function safeDown()
    {
        $this->dropColumn('{{%order}}', 'pay_system');
    }
}
