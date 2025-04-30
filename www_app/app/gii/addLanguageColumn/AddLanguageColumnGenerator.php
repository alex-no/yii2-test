<?php

namespace app\gii\addLanguageColumn;

use Yii;
use yii\gii\Generator;
use yii\db\TableSchema;
use app\gii\addLanguageColumn\SqlCodeFile;

class AddLanguageColumnGenerator extends Generator
{
    const
        POSITION_BEFORE_ALL = 'before_all',
        POSITION_AFTER_ALL = 'after_all';

    public $newLanguageSuffix;
    public $languages = []; // selected languages
    public $position;

    protected $availableLanguages = []; // ALL available languages (retrieved from the database)

    public $executedSql = [];
    public $skippedFields = [];

    public function init(): void
    {
        // Call the parent init method to ensure proper initialization
        // and to set up the default values for the generator.
        parent::init();

        // Populate available languages (from the database)
        $this->availableLanguages = \yii\helpers\ArrayHelper::map(
            Yii::$app->db->createCommand('SELECT `code`, `full_name` FROM `language` WHERE `is_enabled` = 1 ORDER BY `order`')->queryAll(),
            'code',
            function ($row) {
                return "{$row['code']} ({$row['full_name']})";
            }
        );

        // If the list of languages is not yet filled (e.g., when the form is opened for the first time) — set all available
        if (empty($this->languages) && Yii::$app->request->isGet) {
            $this->languages = array_keys($this->availableLanguages);
        }

        Yii::$app->view->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github.min.css');
        Yii::$app->view->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js', [
            'position' => \yii\web\View::POS_END,
            'depends' => [\yii\web\JqueryAsset::class],
        ]);
    }

    public function getName(): string
    {
        return 'Add Language Column';
    }

    public function getDescription(): string
    {
        return 'Adds a new language field (e.g., name_fr) to tables with multilingual fields.';
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [['newLanguageSuffix', 'languages', 'position'], 'required'],
            ['newLanguageSuffix', 'match', 'pattern' => '/^[a-z]{2}$/i', 'message' => 'Language suffix must be 2 letters.'],
            ['languages', 'each', 'rule' => ['string']],
            ['languages', 'validateLanguagesNotEmpty'],
        ]);
    }

    public function validateLanguagesNotEmpty($attribute, $params): void
    {
        if (empty($this->$attribute)) {
            $this->addError($attribute, 'Please select at least one base language.');
        }
    }

    public function attributeLabels(): array
    {
        return [
            'newLanguageSuffix' => 'New Language Suffix (e.g., fr)',
            'languages' => 'Base Languages',
            'position' => 'Position to Insert',
        ];
    }

    public function hints(): array
    {
        return [
            'newLanguageSuffix' => 'Two-letter code for the new language (lowercase or uppercase).',
            'languages' => 'Select which existing languages to consider when finding fields.',
            'position' => 'Choose where to insert the new field among existing localized fields.',
        ];
    }

    public function getAvailableLanguages(): array
    {
        return $this->availableLanguages;
    }

    public function getPositionOptions(): array
    {
        $options = [
            self::POSITION_BEFORE_ALL => 'Before all',
        ];
        foreach ($this->languages as $lang) {
            $options[$lang] = 'After ' . strtoupper($lang);
        }
        $options[self::POSITION_AFTER_ALL] = 'After all';

        // Ensure that "after_all" is selected by default if position is empty
        if ($this->position === null) {
            $this->position = self::POSITION_AFTER_ALL;
        }

        return $options;
    }

    public function generate(): array
    {
        $db = Yii::$app->db;
        $tables = $db->schema->getTableSchemas();
        $files = [];

        foreach ($tables as $table) {
            $localizedGroups = $this->findLocalizedFields($table);
            $allColumns = array_values($table->columns);

            foreach ($localizedGroups as $baseName => $columns) {
                $sql = $this->generateAlterTableSql($table->name, $baseName, $columns, $allColumns);
                if ($sql !== null) {
                    $columnName = $baseName . '_' . $this->newLanguageSuffix;
                    $skip = $this->columnExists($table->name, $columnName);

                    $files[] = new SqlCodeFile(
                        $table->name,
                        $columnName,
                        $sql,
                        $skip
                    );

                    if ($skip) {
                        $this->skippedFields[] = "{$table->name}.{$columnName}";
                    } else {
                        $this->executedSql[] = $sql;
                    }
                }
            }
        }

        return $files;
    }

    private function columnExists(string $tableName, string $columnName): bool
    {
        $schema = Yii::$app->db->getTableSchema($tableName, true);
        return isset($schema->columns[$columnName]);
    }

    public function successMessage()
    {
        $applied = count($this->executedSql);
        $skipped = count($this->skippedFields);

        return "Successfully applied {$applied} changes. Skipped {$skipped} fields.";
    }

    public function formView()
    {
        return '@gii/addLanguageColumn/views/form.php';
    }

    public function findLocalizedFields(TableSchema $tableSchema)
    {
        $localizedFields = [];
        $checkFields = [];
        $countChecked = count($this->languages);

        foreach ($tableSchema->columns as $column) {
            foreach ($this->languages as $lang) {
                if (preg_match('/^(.+)_' . preg_quote($lang, '/') . '$/', $column->name, $matches)) {
                    $baseName = $matches[1];
                    $checkFields[$baseName][$lang] = $column;
                }
            }
        }
        foreach ($checkFields as $baseName => $fields) {
            if (count($fields) == $countChecked) {
                $localizedFields[$baseName] = $fields;
            }
        }
        return $localizedFields;
    }

    public function generateAlterTableSql($tableName, $baseName, $columns, $allColumns): ?string
    {
        if (isset($columns[$this->newLanguageSuffix])) {
            $this->skippedFields[] = "$tableName.{$baseName}_{$this->newLanguageSuffix}";
            return null;
        }

        $columnType = $this->determineColumnType($columns, $allColumns);
        $afterColumn = $this->determineAfterColumn($columns, $allColumns);

        $sql = "ALTER TABLE `{$tableName}` ADD COLUMN `{$baseName}_{$this->newLanguageSuffix}` {$columnType} AFTER `{$afterColumn}`";

        return $sql;
    }

    public function determineColumnType(array $columns, array $allColumns): string
    {
        // "before_all" → type of the first localized column
        if ($this->position === self::POSITION_BEFORE_ALL) {
            return reset($columns)->dbType;
        }
        // if insertion "after" a specific language is specified
        if (isset($columns[$this->position])) {
            return $columns[$this->position]->dbType;
        }
        // by default — the type of the last localized field
        return end($columns)->dbType;
    }

    public function determineAfterColumn(array $columns, array $allColumns): string
    {
        $langKeys = array_keys($columns);

        if ($this->position === self::POSITION_BEFORE_ALL) {
            $firstLocalizedColumn = $columns[$langKeys[0]]->name;

            // Find the index of the field using array_column + array_search
            $columnNames = array_column($allColumns, 'name');
            $index = array_search($firstLocalizedColumn, $columnNames);

            if ($index !== false && $index > 0) {
                return $columnNames[$index - 1];
            }

            // Fallback: the very first field of the table
            return $columnNames[0];
        }

        if (isset($columns[$this->position])) {
            return $columns[$this->position]->name;
        }

        // By default — after the last localized field
        $lastLocalizedColumn = $columns[end($langKeys)];
        return $lastLocalizedColumn->name;
    }
}
