<?php

use yii\db\Migration;

class m250520_030102_modify_development_plan extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('development_plan', 'sort_order', $this->integer()->after('id'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250520_030102_modify_development_plan cannot be reverted.\n";
        $this->dropColumn('development_plan', 'sort_order');

        return false;
    }

}
