<?php

namespace app\api\components;

use Yii;
use yii\rest\Controller;
use app\components\JwtAuth;
use yii\filters\ContentNegotiator;
use yii\web\Response;

class ApiController extends Controller
{
    /**
    * Actions that require authorization (overridden in descendants)
    * Example: ['db-tables', 'update', 'delete']
     */
    protected array $authOnly = [];
    /**
    * Whether to enable JSON responses via ContentNegotiator.
     */
    protected bool $enableJsonNegotiation = true;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        if (!empty($this->authOnly)) {
            $behaviors['authenticator'] = [
                'class' => JwtAuth::class,
                'only' => $this->authOnly,
            ];
        }

        if ($this->enableJsonNegotiation) {
            $behaviors['contentNegotiator'] = [
                'class' => ContentNegotiator::class,
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ];
        }

        return $behaviors;
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (
            in_array($action->id, $this->authOnly, true) &&
            Yii::$app->user->isGuest
        ) {
            Yii::$app->response->statusCode = 401;
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->end(json_encode(['message' => 'Unauthorized']));
        }

        return true;
    }
}
