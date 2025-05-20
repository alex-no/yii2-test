<?php

namespace app\api\modules\v1\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use app\api\components\ApiController;
use app\models\DevelopmentPlan;
use app\components\i18n\AdvActiveDataProvider;

class DevelopmentPlanController extends ApiController
{
    protected array $authOnly = [
        'create',
        'update',
        'delete',
    ];

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'only' => ['create', 'update', 'delete'],
            'rules' => [
                // [
                //     'actions' => ['index', 'view'],
                //     'allow' => true,
                //     'roles' => ['?', '@'],
                // ],
                [
                    'actions' => ['create', 'update', 'delete'],
                    'allow' => true,
                    'roles' => ['roleSuperadmin'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actionIndex()
    {
        $query = DevelopmentPlan::find()
            ->select(['id', 'status', '@@feature', '@@technology', '@@result'])
            ->asArray();

        $dataProvider = new AdvActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20
            ],
            'sort' => [
                'defaultOrder' => [
                    'status' => SORT_ASC,
                    '@@feature' => SORT_ASC,
                ]
            ],
        ]);

        return [
            'items' => $dataProvider->getModels(), // data
            '_meta' => [
                'totalCount' => $dataProvider->getTotalCount(),
                'pageCount' => $dataProvider->pagination->getPageCount(),
                'currentPage' => $dataProvider->pagination->getPage() + 1,
                'perPage' => $dataProvider->pagination->getPageSize(),
            ],
        ];
    }

    public function actionView($id)
    {
        return $this->findModel($id);
    }

    public function actionCreate()
    {
        $model = new DevelopmentPlan();

        if ($this->request->isPost) {
            if ($model->load($this->request->post(), '') && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $model;
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPut && $model->load($this->request->post(), '') && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $model;
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return ['success' => true];
    }

    /**
     * Finds the DevelopmentPlan model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return DevelopmentPlan the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = DevelopmentPlan::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
