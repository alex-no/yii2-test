<?php

namespace app\gii\addLanguageColumn;

use Yii;
use yii\gii\CodeFile;

class SqlCodeFile extends CodeFile
{
    public string $tableName;
    public string $columnName;
    public bool $skip = false;

    public function __construct(
        string $tableName,
        string $columnName,
        string $content // ready SQL
    ) {
        // Call the parent constructor — a fake "path" is needed for compatibility with Gii
        parent::__construct(
            '@db/' . $tableName . '_' . $columnName . '.sql',
            $content
        );

        $this->tableName = $tableName;
        $this->columnName = $columnName;
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

    public function getName()
    {
        return "`{$this->tableName}`.`{$this->columnName}`";
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

    public function getOperation()
    {
        return $this->skip ? self::OP_SKIP : self::OP_CREATE;
    }

    public function getOperationDescription()
    {
        return $this->skip ? 'Already exists (skipped)' : 'Add column';
    }

}
