<?php

namespace app\api\modules\v1\controllers;

use yii\rest\Controller;
use yii\filters\auth\HttpBearerAuth;
use yii\web\Response;
use app\components\JwtAuth;
use Yii;

class UserController extends Controller
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            //'class' => HttpBearerAuth::class,
            'class' => JwtAuth::class,
        ];

        $behaviors['contentNegotiator']['formats']['application/json'] = Response::FORMAT_JSON;

        return $behaviors;
    }

    public function actionProfile()
    {
        return Yii::$app->user->identity;
    }

    public function actionLogout()
    {
        // JWT cannot be "deleted", but a blacklist can be implemented
        return ['message' => 'Logged out (client must delete token)'];
    }

}
