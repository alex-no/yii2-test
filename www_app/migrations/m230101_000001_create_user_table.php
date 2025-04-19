<?php

use yii\db\Migration;

class m230101_000001_create_user_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci ENGINE=InnoDB';

        $this->createTable('{{%user}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'language_code' => $this->string(2)->defaultValue(null),
            'name' => $this->string(191)->notNull(),
            'email' => $this->string(191)->notNull(),
            'email_verified_at' => $this->timestamp()->defaultValue(null),
            'password' => $this->string(191)->notNull(),
            'auth_key' => $this->string(32)->notNull(),
            'phone' => $this->string(16)->defaultValue(null),
            'remember_token' => $this->string(100)->defaultValue(null),
            'created_at' => $this->timestamp()->defaultValue(null),
            'updated_at' => $this->timestamp()->defaultValue(null),
        ], $tableOptions);

        // Индексы
        $this->createIndex('user_email_unique', '{{%user}}', 'email', true);
        $this->createIndex('user_language_code_foreign', '{{%user}}', 'language_code');

        // Внешний ключ
        $this->addForeignKey(
            'user_language_code_foreign',
            '{{%user}}',
            'language_code',
            '{{%language}}',
            'code',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('user_language_code_foreign', '{{%user}}');
        $this->dropIndex('user_language_code_foreign', '{{%user}}');
        $this->dropIndex('user_email_unique', '{{%user}}');
        $this->dropTable('{{%user}}');
    }
}
