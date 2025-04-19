<?php

namespace app\gii;

use Yii;
use yii\gii\generators\model\Generator;
use yii\gii\CodeFile;

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
            'generateChildClass' => 'Generate child model',
        ]);
    }

    public function hints()
    {
        return array_merge(parent::hints(), [
            'generateChildClass' => 'If checked, an empty child class will be created for your logic.',
        ]);
    }

    public function stickyAttributes()
    {
        return array_merge(parent::stickyAttributes(), ['generateChildClass']);
    }

    public function generate()
    {
        $files = parent::generate();
        $modelClass = $this->getModelClass();
        $baseFileName = $modelClass . '.php';
    
        foreach ($files as $i => $file) {
            if (str_ends_with($file->path, $baseFileName)) {
                $files[$i]->path = dirname($file->path) . '/TopModels/' . $modelClass . 'Base.php';
                break;
            }
        }
    
        if ($this->generateChildClass) {
            $childPath = Yii::getAlias('@' . str_replace('\\', '/', $this->ns)) . '/' . $modelClass . '.php';
            if (!file_exists($childPath)) {
                $files[] = new CodeFile(
                    $childPath,
                    $this->render('model-child.php', [
                        'className' => $modelClass,
                    ])
                );
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

    public function requiredTemplates()
    {
        return ['model.php', 'model-child.php'];
    }
    
    public function formView()
    {
        return '@app/app/gii/form.php';
    }    
}
