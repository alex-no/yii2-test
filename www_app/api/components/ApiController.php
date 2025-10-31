<?php

namespace app\api\components;

use Yii;
use yii\rest\Controller;
use app\components\JwtAuth;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use AlexNo\FieldLingo\Adapters\Yii2\LingoActiveDataProvider;

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
    protected function getListMeta(LingoActiveDataProvider $dataProvider): array
    {
        $pagination = $dataProvider->pagination;
        $currentPage = $pagination->getPage(); // 0-based
        $pageCount = $pagination->getPageCount(); // total pages
        $maxVisible = 5;

        $pageLinks = [];

        if ($pageCount <= $maxVisible + 2) {
            // Simple pagination without ellipsis
            $pageRange = range(0, $pageCount - 1);
        } else {
            $pageRange = [];

            $start = max(1, $currentPage - 1);
            $end = min($pageCount - 2, $currentPage + 1);

            $pageRange[] = 0;

            if ($start > 1) {
                $pageRange[] = 'ellipsis-start';
            }

            foreach (range($start, $end) as $i) {
                $pageRange[] = $i;
            }

            if ($end < $pageCount - 2) {
                $pageRange[] = 'ellipsis-end';
            }

            $pageRange[] = $pageCount - 1;
        }

        foreach ($pageRange as $i) {
            if ($i === 'ellipsis-start' || $i === 'ellipsis-end') {
                $pageLinks[] = [
                    'label' => '...',
                    'url' => null,
                    'active' => false,
                ];
            } else {
                $pageLinks[] = [
                    'label' => (string)($i + 1),
                    'url' => $pagination->createUrl($i),
                    'active' => $i === $currentPage,
                ];
            }
        }

        // Add "previous" and "next"
        array_unshift($pageLinks, [
            'label' => 'previous',
            'url' => $currentPage > 0 ? $pagination->createUrl($currentPage - 1) : null,
            'active' => false,
        ]);
        $pageLinks[] = [
            'label' => 'next',
            'url' => $currentPage + 1 < $pageCount ? $pagination->createUrl($currentPage + 1) : null,
            'active' => false,
        ];

        return [
            'page' => $currentPage + 1,
            'totalCount' => $dataProvider->getTotalCount(),
            'pageCount' => $pageCount,
            'currentPage' => $currentPage + 1,
            'perPage' => $pagination->getPageSize(),
            'links' => [
                'first' => $pagination->createUrl(0),
                'last' => $pagination->createUrl($pageCount - 1),
            ],
            'pageLinks' => $pageLinks,
        ];
    }

}
