<?php
// Path: base/Application.php
namespace app\base;

use yii\web\Application as YiiApplication;
use Yii;

class Application extends YiiApplication
{
    public function setVendorPath($path)
    {
        // Set aliases as needed
        parent::setVendorPath($path);
        Yii::setAlias('@bower', '@vendor/bower-asset');
    }
}