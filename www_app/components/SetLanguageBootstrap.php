<?php

namespace app\components;

use Yii;
use yii\base\BootstrapInterface;

class SetLanguageBootstrap implements BootstrapInterface
{
    public bool $isApi = false;

    public function bootstrap($app)
    {
        $lang = Yii::$app->languageSelector->detect($this->isApi);
        Yii::$app->language = $lang;
    }
}
