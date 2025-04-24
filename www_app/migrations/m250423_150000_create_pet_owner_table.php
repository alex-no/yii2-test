<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%pet_owner}}`.
 */
class m250423_150000_create_pet_owner_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%pet_owner}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->bigInteger()->unsigned()->notNull(),
            'pet_type_id' => $this->integer()->unsigned()->notNull(),
            'pet_breed_id' => $this->integer()->unsigned()->null(),
            'nickname_uk' => $this->string(255),
            'nickname_en' => $this->string(255),
            'nickname_ru' => $this->string(255),
            'year_of_birth' => 'MEDIUMINT',
            'created_at' => $this->timestamp()->defaultValue(null),
            'updated_at' => $this->timestamp()->defaultValue(null),
        ]);

        // Indexes
        $this->createIndex('fk_pet_type_id2_idx', '{{%pet_owner}}', 'pet_type_id');
        $this->createIndex('fk_pet_breed_id1_idx', '{{%pet_owner}}', 'pet_breed_id');
        $this->createIndex('fk_user_id1_idx', '{{%pet_owner}}', 'user_id', false);

        // Foreign Keys
        $this->addForeignKey(
            'fk_user_id1',
            '{{%pet_owner}}',
            'user_id',
            '{{%user}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_pet_type_id2',
            '{{%pet_owner}}',
            'pet_type_id',
            '{{%pet_type}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_pet_breed_id1',
            '{{%pet_owner}}',
            'pet_breed_id',
            '{{%pet_breed}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        // Data insertion
        $this->batchInsert('{{%pet_owner}}', [
            'id', 'user_id', 'pet_type_id', 'pet_breed_id',
            'nickname_uk', 'nickname_en', 'nickname_ru',
            'year_of_birth', 'created_at', 'updated_at'
        ], [
            [1, 1, 1, 1, 'Лорд', 'Lord', 'Лорд', 2020, null, null],
            [2, 1, 1, 11, 'Рік', 'Rik', 'Рик', 2010, null, null],
            [3, 1, 1, 11, 'Ірж', 'Irj', 'Ирж', 2014, null, null],
        ]);
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_user_id1', '{{%pet_owner}}');
        $this->dropForeignKey('fk_pet_type_id2', '{{%pet_owner}}');
        $this->dropForeignKey('fk_pet_breed_id1', '{{%pet_owner}}');

        $this->dropIndex('fk_user_id1_idx', '{{%pet_owner}}');
        $this->dropIndex('fk_pet_type_id2_idx', '{{%pet_owner}}');
        $this->dropIndex('fk_pet_breed_id1_idx', '{{%pet_owner}}');

        $this->dropTable('{{%pet_owner}}');
    }
}
