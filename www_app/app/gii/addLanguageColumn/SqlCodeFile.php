<?php

namespace app\gii\addLanguageColumn;

use Yii;
use yii\gii\CodeFile;

class SqlCodeFile extends CodeFile
{
    public string $tableName;
    public string $columnName;
    //public string $operationDescription;
    public bool $skip;

    public function __construct(
        string $tableName,
        string $columnName,
        string $content, // ready SQL
        bool $skip = false
    ) {
        // Call the parent constructor — a fake "path" is needed for compatibility with Gii
        parent::__construct(
            '@db/' . $tableName . '_' . $columnName . '.sql',
            $content
        );

        $this->tableName = $tableName;
        $this->columnName = $columnName;
        $this->skip = $skip;

        $this->operation = $skip ? parent::OP_SKIP : parent::OP_CREATE;
        //$this->operation = $skip ? 'Already exists (skipped)' : 'Add column';
    }

    public function save(): bool
    {
        if ($this->skip) {
            return true;
        }

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
        $escapedSql = htmlspecialchars($this->content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return <<<HTML
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<pre class="hljs sql">{$escapedSql}</pre>
<script type="text/javascript">hljs.highlightAll();</script>
HTML;
//<script type="text/javascript">alert(1);window.onload = function() {initHighlighting();};alert(2);</script>
    }

    public function getRelativePath()
    {
        return "Table `{$this->tableName}` — Add column `{$this->columnName}`";
    }
}
