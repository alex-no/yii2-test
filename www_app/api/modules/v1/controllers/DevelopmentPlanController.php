<?php

namespace app\api\modules\v1\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use app\api\components\ApiController;
use app\models\DevelopmentPlan;
use app\components\i18n\AdvActiveDataProvider;

/**
 * DevelopmentPlanController implements the CRUD actions for DevelopmentPlan model.
 *
 * @OA\Tag(
 *     name="DevelopmentPlan",
 *     description="API for working with Development Plan"
 * )
 * @OA\Schema(
 *     schema="DevelopmentPlan",
 *     title="Development Plan",
 *     required={"sort_order", "status", "feature", "technology"},
 *     @OA\Property(property="sort_order", type="integer"),
 *     @OA\Property(property="status", type="string"),
 *     @OA\Property(property="feature", type="string"),
 *     @OA\Property(property="technology", type="string"),
 *     @OA\Property(property="result", type="string", nullable=true)
 * )
 */
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


    /**
     * Lists all DevelopmentPlan models.
     *
     * @return array
     *
     * @OA\Get(
     *     path="/api/development-plan?status={status}",
     *     operationId="getDevelopmentPlans",
     *     summary="Retrieve a list of Development Plans",
     *     description="Returns a list of Development Plans from the database",
     *     tags={"About system"},
     *     @OA\Parameter(
     *         name="status",
     *         description="Status of Plan",
     *         in="query",
     *         @OA\Schema(type="string", enum={"pending", "in_progress", "completed"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=11),
     *                     @OA\Property(property="sort_order", type="integer"),
     *                     @OA\Property(property="status", type="string", example="in_progress", description="Status of Feature"),
     *                     @OA\Property(property="feature", type="string", example="REST API", description="Feature Name"),
     *                     @OA\Property(property="technology", type="string", example="Yii2, PHP", description="Technology of the feature"),
     *                     @OA\Property(property="result", type="string", nullable=true, example="API", description="result of the feature"),
     *                 ),
     *                 @OA\Property(
     *                     property="_meta",
     *                     type="object",
     *                     @OA\Property(property="totalCount", type="integer", example=16),
     *                     @OA\Property(property="pageCount", type="integer", example=2),
     *                     @OA\Property(property="currentPage", type="integer", example=2),
     *                     @OA\Property(property="perPage", type="integer", example=10)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Development Plan not found"
     *     )
     * )
     */
    public function actionIndex($status = null)
    {
        $where = [];
        if (!empty($status)) {
            $where['status'] = $status;
        }
        $query = DevelopmentPlan::find()
            ->select([
                'id',
                'sort_order',
                'status',
                'feature' => '@@feature',
                'technology' => '@@technology',
                'result' =>'@@result'
            ])
            ->where($where)
            ->asArray();

        $dataProvider = new AdvActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 16
            ],
            'sort' => [
                'defaultOrder' => [
                    'status' => SORT_DESC,
                    'sort_order' => SORT_ASC,
                ]
            ],
        ]);

        return [
            'items' => array_map(function($row) {
                $row['status_adv'] = DevelopmentPlan::makeStatusAdv($row['status']);
                return $row;
            }, $dataProvider->getModels()),
            '_meta' => $this->getListMeta($dataProvider),
        ];
    }

    /**
     * Displays a single Development Plan model.
     *
     * @param int $id ID
     * @return array|DevelopmentPlan
     * @throws NotFoundHttpException if the model cannot be found
     *
     * @OA\Get(
     *     path="/api/development-plan/{id}",
     *     summary="Get a single Development Plan details",
     *     description="Returns details of a single Development Plan",
     *     tags={"About system"},
     *     operationId="getDevelopmentPlanById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the Development Plan",
     *         @OA\Schema(type="integer", example=11)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=11),
     *             @OA\Property(property="sort_order", type="integer", example=10),
     *             @OA\Property(property="status", type="string", example="in_progress", description="Status of Feature"),
     *             @OA\Property(property="feature_uk", type="string", example="REST API", description="Feature Name in Ukrainian"),
     *             @OA\Property(property="feature_en", type="string", example="REST API", description="Feature Name in English"),
     *             @OA\Property(property="feature_ru", type="string", example="REST API", description="Feature Name in Russian"),
     *             @OA\Property(property="technology_uk", type="string", example="Yii2, PHP", description="Technology of the feature in Ukrainian"),
     *             @OA\Property(property="technology_en", type="string", example="Yii2, PHP", description="Technology of the feature in English"),
     *             @OA\Property(property="technology_ru", type="string", example="Yii2, PHP", description="Technology of the feature in Russian"),
     *             @OA\Property(property="result_uk", type="string", nullable=true, example="API", description="result of the feature in Ukrainian"),
     *             @OA\Property(property="result_en", type="string", nullable=true, example="API", description="result of the feature in English"),
     *             @OA\Property(property="result_ru", type="string", nullable=true, example="API", description="result of the feature in Russian"),
     *             @OA\Property(property="status_adv", type="string", example="🔧 In Progress", description="Status with icon"),
     *             @OA\Property(property="updated_at", type="datetime", example="2025-03-12T20:08:04.566Z", description="Date and time of the last update"),
     *             @OA\Property(property="created_at", type="datetime", example="2025-03-12T20:08:04.566Z", description="Date and time of the creation")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="DevelopmentPlan not found"
     *     )
     * )
     */
    public function actionView($id)
    {
        return $this->findModel($id);
    }

    /**
     * Creates a new Development Plan model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     *
     * @OA\Post(
     *     path="/api/development-plan",
     *     security={{"bearerAuth":{}}},
     *     summary="Create a new Development Plan model",
     *     tags={"About system"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"status", "feature_uk", "feature_en", "feature_ru"},
     *             @OA\Property(property="sort_order", type="integer", example=10),
     *             @OA\Property(property="status", type="string", example="in_progress", description="Status of Feature"),
     *             @OA\Property(property="feature_uk", type="string", example="REST API", description="Feature Name in Ukrainian"),
     *             @OA\Property(property="feature_en", type="string", example="REST API", description="Feature Name in English"),
     *             @OA\Property(property="feature_ru", type="string", example="REST API", description="Feature Name in Russian"),
     *             @OA\Property(property="technology_uk", type="string", example="Yii2, PHP", description="Technology of the feature in Ukrainian"),
     *             @OA\Property(property="technology_en", type="string", example="Yii2, PHP", description="Technology of the feature in English"),
     *             @OA\Property(property="technology_ru", type="string", example="Yii2, PHP", description="Technology of the feature in Russian"),
     *             @OA\Property(property="result_uk", type="string", nullable=true, example="API", description="result of the feature in Ukrainian"),
     *             @OA\Property(property="result_en", type="string", nullable=true, example="API", description="result of the feature in English"),
     *             @OA\Property(property="result_ru", type="string", nullable=true, example="API", description="result of the feature in Russian"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Created"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     )
     * )
     */
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

    /**
     * Updates an existing Development Plan model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     *
     * @OA\Put(
     *     path="/api/development-plan/{id}",
     *     security={{"bearerAuth":{}}},
     *     summary="Update a Development Plan model",
     *     tags={"About system"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="sort_order", type="integer", example=10),
     *             @OA\Property(property="status", type="string", example="in_progress", description="Status of Feature"),
     *             @OA\Property(property="feature_uk", type="string", example="REST API", description="Feature Name in Ukrainian"),
     *             @OA\Property(property="feature_en", type="string", example="REST API", description="Feature Name in English"),
     *             @OA\Property(property="feature_ru", type="string", example="REST API", description="Feature Name in Russian"),
     *             @OA\Property(property="technology_uk", type="string", example="Yii2, PHP", description="Technology of the feature in Ukrainian"),
     *             @OA\Property(property="technology_en", type="string", example="Yii2, PHP", description="Technology of the feature in English"),
     *             @OA\Property(property="technology_ru", type="string", example="Yii2, PHP", description="Technology of the feature in Russian"),
     *             @OA\Property(property="result_uk", type="string", nullable=true, example="API", description="result of the feature in Ukrainian"),
     *             @OA\Property(property="result_en", type="string", nullable=true, example="API", description="result of the feature in English"),
     *             @OA\Property(property="result_ru", type="string", nullable=true, example="API", description="result of the feature in Russian"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Updated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     )
     * )
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPut && $model->load($this->request->post(), '') && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $model;
    }

    /**
     * Deletes an existing DevelopmentPlan model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     *
     * @OA\Delete(
     *     path="/api/development-plan/{id}",
     *     security={{"bearerAuth":{}}},
     *     operationId="delete Development Plan",
     *     summary="Delete a Development Plan",
     *     tags={"About system"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found"
     *     ),
     * )
     */
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
