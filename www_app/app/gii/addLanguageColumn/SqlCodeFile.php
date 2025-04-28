<?php

namespace app\gii\addLanguageColumn;

use Yii;
use yii\gii\CodeFile;

class SqlCodeFile extends CodeFile
{
    public string $tableName;
    public string $columnName;

    public function __construct($path, $content, $config = [])
    {
        parent::__construct($path, $content, $config);

        // Override path to display nicely in Gii
        $this->path = sprintf('Add column "%s" to table "%s"', $this->columnName, $this->tableName);
    }

    public function save()
    {
        try {
            Yii::$app->db->createCommand($this->content)->execute();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function preview()
    {
        $highlightScript = '<script>hljs.highlightAll();</script>';
        return '<pre class="hljs sql">' . htmlspecialchars($this->content) . '</pre>' . $highlightScript;
    }

    public function getOperationDescription()
    {
        return 'Apply SQL';
    }
}
