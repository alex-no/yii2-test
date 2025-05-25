<?php

namespace app\api\components;

use Yii;
use yii\rest\Controller;
use app\components\JwtAuth;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use app\components\i18n\AdvActiveDataProvider;

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

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
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

    /**
     * Get metadata for paginated list responses.
     * This method is used to provide pagination metadata for API responses.
     *
     * @param \yii\data\ActiveDataProvider $dataProvider
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    protected function getListMeta(AdvActiveDataProvider $dataProvider): array
    {
        $pagination = $dataProvider->pagination;
        $page = $pagination->getPage();
        $count = $pagination->getPageCount();

        $pageLinks = array_map(
            fn(int $i) => [
                'label' => (string)($i + 1),
                'url' => $pagination->createUrl($i),
                'active' => $i === $page,
            ],
            range(0, $count - 1)
        );

        return [
            'page' => $page + 1,
            'totalCount' => $dataProvider->getTotalCount(),
            'pageCount' => $count,
            'currentPage' => $page + 1,
            'perPage' => $pagination->getPageSize(),
            'links' => [
                'first' => $pagination->createUrl(0),
                'last' => $pagination->createUrl($count - 1),
                'prev' => $page > 0 ? $pagination->createUrl($page - 1) : null,
                'next' => $page + 1 < $count ? $pagination->createUrl($page + 1) : null,
            ],
            'pageLinks' => $pageLinks,
        ];
    }
}
