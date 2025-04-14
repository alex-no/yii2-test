<?php
// Path: api/modules/v1/controllers/SiteController.php

namespace app\api\modules\v1\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => \yii\filters\ContentNegotiator::class,
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $app = Yii::$app;

        return [
            'api' => 'Test API',
            'projectName' => $app->name,
            'version' => $app->version,
            'language' => $app->language,
            'timeZone' => $app->timeZone,
        ];
    }
}
