<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%pet_type}}` with initial data.
 */
class m250423_100000_create_pet_types_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%pet_type}}', [
            'id' => $this->primaryKey()->unsigned(),
            'name_uk' => $this->string()->defaultValue(null),
            'name_en' => $this->string()->defaultValue(null),
            'name_ru' => $this->string()->defaultValue(null),
            'created_at' => $this->timestamp()->defaultValue(null),
            'updated_at' => $this->timestamp()->defaultValue(null),
        ]);

        $this->batchInsert('{{%pet_type}}', [
            'id', 'name_uk', 'name_en', 'name_ru', 'created_at', 'updated_at'
        ], [
            [1, 'собака', 'dog', 'собака', '2025-03-12 03:15:10', '2025-03-12 03:15:10'],
            [2, 'кішка', 'cat', 'кошка', '2025-03-12 03:15:10', '2025-03-12 03:15:10'],
            [3, 'гризун', 'rodent', 'грызун', '2025-03-12 03:15:10', '2025-03-12 03:15:10'],
            [4, 'птах', 'bird', 'птица', '2025-03-12 03:15:10', '2025-03-12 03:15:10'],
            [5, 'риба', 'fish', 'рыба', '2025-03-12 03:15:10', '2025-03-12 03:15:10'],
            [6, 'плазуне', 'reptile', 'пресмыкающееся', '2025-03-12 03:15:10', '2025-03-12 03:15:10'],
            [7, 'павук', 'spider', 'паук', '2025-03-12 03:15:10', '2025-03-12 03:15:10'],
            [8, 'комаха', 'insect', 'насекомое', '2025-03-12 03:15:10', '2025-03-12 03:15:10'],
            [9, 'інше', 'other', 'другое', '2025-03-12 03:15:10', '2025-03-12 03:15:10'],
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%pet_type}}');
    }
}
