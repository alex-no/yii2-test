<?php

use yii\db\Migration;

class m250414_224926_language extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
            CREATE TABLE `language` (
                `code` VARCHAR(2) NOT NULL,
                `short_name` VARCHAR(3) NOT NULL,
                `full_name` VARCHAR(32) NOT NULL,
                `is_enabled` TINYINT(1) NOT NULL DEFAULT '1',
                `order` TINYINT(4) NOT NULL,
            PRIMARY KEY (`code`))
            ENGINE = InnoDB
            DEFAULT CHARACTER SET = utf8mb4
        ");


        $this->insert('language', [
            'code' => 'uk',
            'short_name' => 'Укр',
            'full_name' => 'Українська',
            'is_enabled' => 1,
            'order' => 1,
        ]);
        $this->insert('language', [
            'code' => 'en',
            'short_name' => 'Eng',
            'full_name' => 'English',
            'is_enabled' => 1,
            'order' => 2,
        ]);
        $this->insert('language', [
            'code' => 'fr',
            'short_name' => 'Рус',
            'full_name' => 'Русский',
            'is_enabled' => 1,
            'order' => 3,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250414_224926_language cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250414_224926_language cannot be reverted.\n";

        return false;
    }
    */
}
