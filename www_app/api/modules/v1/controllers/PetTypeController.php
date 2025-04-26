<?php

namespace app\api\modules\v1\controllers;

use Yii;
use app\models\PetType;
use yii\data\ActiveDataProvider;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PetTypeController implements the CRUD actions for PetType model.
 */
class PetTypeController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * @OA\Get(
     *     path="/pet-type",
     *     summary="List all Pet Types",
     *     tags={"PetType"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/PetType"))
     *     )
     * )
     *
     * Lists all PetType models.
     */
    public function actionIndex()
    {
        $query = PetType::find()
            ->select(['id', '@@name'])
            // ->select([
            //     "id" => "id",
            //     "name" => "@@name",
            // ])
            ->orderBy(['@@name' => SORT_ASC])
            ->asArray();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 5
            ],
            // 'sort' => [
            //     'defaultOrder' => [
            //         '@@name' => SORT_DESC,
            //     ]
            // ],
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

    /**
     * @OA\Get(
     *     path="/pet-type/{id}",
     *     summary="View a Pet Type",
     *     tags={"PetType"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Pet Type ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/PetType")
     *     ),
     *     @OA\Response(response=404, description="Pet Type not found")
     * )
     *
     * Displays a single PetType model.
     */
    public function actionView($id)
    {
        return $this->findModel($id);
    }

    /**
     * @OA\Post(
     *     path="/pet-type",
     *     summary="Create a new Pet Type",
     *     tags={"PetType"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PetType")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pet Type created",
     *         @OA\JsonContent(ref="#/components/schemas/PetType")
     *     ),
     *     @OA\Response(response=400, description="Invalid input")
     * )
     *
     * Creates a new PetType model.
     */
    public function actionCreate()
    {
        $model = new PetType();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $model;
    }

    /**
     * @OA\Put(
     *     path="/pet-type/{id}",
     *     summary="Update an existing Pet Type",
     *     tags={"PetType"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Pet Type ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PetType")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pet Type updated",
     *         @OA\JsonContent(ref="#/components/schemas/PetType")
     *     ),
     *     @OA\Response(response=404, description="Pet Type not found")
     * )
     *
     * Updates an existing PetType model.
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $model;
    }

    /**
     * @OA\Delete(
     *     path="/pet-type/{id}",
     *     summary="Delete a Pet Type",
     *     tags={"PetType"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Pet Type ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Pet Type deleted"),
     *     @OA\Response(response=404, description="Pet Type not found")
     * )
     *
     * Deletes an existing PetType model.
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return ['success' => true];
    }

    /**
     * Finds the PetType model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return PetType the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PetType::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
