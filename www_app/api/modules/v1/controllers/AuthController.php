<?php
namespace app\api\modules\v1\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use app\models\User;
use yii\web\BadRequestHttpException;

class AuthController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator']['formats']['application/json'] = Response::FORMAT_JSON;
        return $behaviors;
    }

    public function actionRegister()
    {
        $body = Yii::$app->request->bodyParams;

        $user = new User();
        $user->username = $body['username'] ?? null;
        $user->email = $body['email'] ?? null;
        $user->phone = $body['phone'] ?? null;
        $user->setPassword($body['password'] ?? '');
        $user->generateAuthData();
        $user->created_at = time();
        $user->updated_at = time();

        if ($user->save()) {
            return ['success' => true];
        }

        throw new BadRequestHttpException(json_encode($user->getErrors()));
    }

    public function actionLogin()
    {
        $body = Yii::$app->request->bodyParams;
        $user = User::findByUsername($body['username'] ?? '');

        if ($user && $user->validatePassword($body['password'] ?? '')) {
            return ['access_token' => $user->access_token];
        }

        throw new BadRequestHttpException('Invalid username or password');
    }
}
