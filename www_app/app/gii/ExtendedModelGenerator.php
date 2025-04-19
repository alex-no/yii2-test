<?php

namespace app\gii;

use Yii;
use yii\gii\generators\model\Generator;

class ExtendedModelGenerator extends Generator
{
    public $generateChildClass = true;

    public function rules()
    {
        return array_merge(parent::rules(), [
            ['generateChildClass', 'boolean'],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'generateChildClass' => 'Generate child model (for custom logic)',
        ]);
    }

    public function stickyAttributes()
    {
        return array_merge(parent::stickyAttributes(), ['generateChildClass']);
    }

    public function generate()
    {
        $files = parent::generate();

        if ($this->generateChildClass) {
            $class = $this->getModelClass();
            $childPath = Yii::getAlias('@' . str_replace('\\', '/', $this->ns)) . '/' . $class . '.php';
            if (!file_exists($childPath)) {
                $files[] = new \yii\gii\CodeFile(
                    $childPath,
                    $this->render('model-child.php', [
                        'className' => $class,
                    ])
                );
            }
        }

        // Override the path for the base model
        foreach ($files as $file) {
            if (str_ends_with($file->path, "{$this->getModelClass()}.php")) {
                $file->path = dirname($file->path) . '/TopModels/' . $this->getModelClass() . 'Base.php';
            }
        }

        return $files;
    }
    public function getModelClass()
    {
        // Check if the model class is set
        if (empty($this->modelClass)) {
            throw new \RuntimeException('modelClass is not set.');
        }
        return basename(str_replace('\\', '/', $this->modelClass));
    }
}
