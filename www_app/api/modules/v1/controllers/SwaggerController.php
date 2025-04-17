<?php

namespace app\api\modules\v1\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\components\SwaggerLogger;
use OpenApi\Generator;


/**
 * Swagger UI & JSON Generator
 */
class SwaggerController extends Controller
{
    public $enableCsrfValidation = false;

    /**
     * @return array
     * Swagger JSON output
     */
    public function actionJson()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $logger = new SwaggerLogger();
        return Generator::scan(
            [Yii::getAlias('@app/api/modules/v1/controllers')],
            ['logger' => $logger]
        );
    }

    /**
     * Render Swagger UI
     */
    // public function actionUi()
    // {
    //     return $this->renderFile('@vendor/zircote/swagger-php/swagger-ui/dist/index.html');
    // }
}
