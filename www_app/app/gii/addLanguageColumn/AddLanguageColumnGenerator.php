<?php

namespace app\gii\addLanguageColumn;

use Yii;
use yii\gii\Generator;
use yii\db\TableSchema;
use yii\helpers\StringHelper;

class AddLanguageColumnGenerator extends Generator
{
    public $newLanguageSuffix;
    public $languages = [];
    public $position; // 'before all', lang code, or 'after all'

    public $executedSql = [];
    public $skippedFields = [];

    public function getName()
    {
        return 'Add Language Column';
    }

    public function getDescription()
    {
        return 'Adds a new language field (e.g., name_fr) to tables with multilingual fields.';
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['newLanguageSuffix', 'languages', 'position'], 'required'],
            ['newLanguageSuffix', 'match', 'pattern' => '/^[a-z]{2}$/i', 'message' => 'Language suffix must be 2 letters.'],
        ]);
    }

    public function attributeLabels()
    {
        return [
            'newLanguageSuffix' => 'New Language Suffix (e.g., fr)',
            'languages' => 'Base Languages',
            'position' => 'Position to Insert',
        ];
    }

    public function hints()
    {
        return [
            'newLanguageSuffix' => 'Two-letter code for the new language (lowercase or uppercase).',
            'languages' => 'Select which existing languages to consider when finding fields.',
            'position' => 'Choose where to insert the new field among existing localized fields.',
        ];
    }

    public function getAvailableLanguages()
    {
        return \yii\helpers\ArrayHelper::map(
            Yii::$app->db->createCommand('SELECT `code`, `full_name` FROM `language` WHERE `is_enabled` = 1 ORDER BY `order`')->queryAll(),
            'code',
            function($row) {
                return "{$row['code']} ({$row['full_name']})";
            }
        );
    }

    public function getPositionOptions()
    {
        $options = [
            'before_all' => 'Before all',
        ];
        foreach ($this->languages as $lang) {
            $options[$lang] = 'After ' . strtoupper($lang);
        }
        $options['after_all'] = 'After all';
        return $options;
    }

    public function generate()
    {
        $db = Yii::$app->db;
        $tables = $db->schema->getTableSchemas();

        foreach ($tables as $table) {
            $localizedGroups = $this->findLocalizedFields($table);
            foreach ($localizedGroups as $baseName => $columns) {
                $sql = $this->generateAlterTableSql($table->name, $baseName, $columns);
                if ($sql !== null) {
                    $this->executedSql[] = $sql;
                    $db->createCommand($sql)->execute();
                }
            }
        }

        return [];
    }

    public function formView()
    {
        return '@gii/addLanguageColumn/views/form.php';
    }

    public function findLocalizedFields(TableSchema $tableSchema)
    {
        $localizedFields = [];
        foreach ($tableSchema->columns as $column) {
            foreach ($this->languages as $lang) {
                if (preg_match('/^(.+)_' . preg_quote($lang, '/') . '$/', $column->name, $matches)) {
                    $baseName = $matches[1];
                    $localizedFields[$baseName][$lang] = $column;
                }
            }
        }
        return $localizedFields;
    }

    public function generateAlterTableSql($tableName, $baseName, $columns)
    {
        if (isset($columns[$this->newLanguageSuffix])) {
            $this->skippedFields[] = "$tableName.{$baseName}_{$this->newLanguageSuffix}";
            return null;
        }

        $columnType = reset($columns)->dbType;
        $afterColumn = $this->determineAfterColumn($columns);

        $sql = "ALTER TABLE `{$tableName}` ADD COLUMN `{$baseName}_{$this->newLanguageSuffix}` {$columnType}";

        if ($afterColumn) {
            $sql .= " AFTER `{$afterColumn}`";
        }

        return $sql;
    }

    public function determineAfterColumn($columns)
    {
        if ($this->position === 'before_all') {
            return null; // MySQL doesn't support BEFORE in ADD COLUMN, unless moving afterwards separately
        }

        if ($this->position === 'after_all') {
            $langs = array_keys($columns);
            $lastLang = end($langs);
            return $columns[$lastLang]->name ?? null;
        }

        if (isset($columns[$this->position])) {
            return $columns[$this->position]->name;
        }

        return null; // By default, no AFTER
    }
}
