<?php

use yii\db\Migration;

class m250526_112410_insert_into_development_plan extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('{{%development_plan}}', [
            'sort_order' => 5,
            'status' => 'in_progress',
            'feature_en' => 'Frontend for development plan',
            'feature_uk' => 'Frontend для плану розробки',
            'feature_ru' => 'Frontend для плана разработки',
            'technology_en' => 'PHP, Yii 2, Vue 3, bootstrap',
            'technology_uk' => 'PHP, Yii 2, Vue 3, bootstrap',
            'technology_ru' => 'PHP, Yii 2, Vue 3, bootstrap',
            'result_en' => 'Displaying the development plan as a table with a language switcher',
            'result_uk' => 'Відображення плану розробки у вигляді таблиці з перемикачем мови',
            'result_ru' => 'Отображение плана разработки в виде таблицы, с переключателем языка',
            'created_at' => time(),
            'updated_at' => time(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250526_112410_insert_into_development_plan cannot be reverted.\n";

        return false;
    }

}
