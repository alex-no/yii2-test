<?php

namespace app\api\modules\v1\controllers;

use app\models\PetBreed;
use yii\data\ActiveDataProvider;
use app\api\components\ApiController;
use yii\web\NotFoundHttpException;
use app\components\i18n\AdvActiveDataProvider;

/**
 * PetBreedController implements the CRUD actions for PetBreed model.
 *
 * @OA\Schema(
 *     schema="PetBreed",
 *     required={"name", "pet_type_id"},
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="pet_type_id", type="integer"),
 *     @OA\Property(property="description", type="string", nullable=true)
 * )
 */
class PetBreedController extends ApiController
{
    /**
     * Lists all PetBreed models.
     *
     * @return string
     *
     * @OA\Get(
     *     path="/api/pet-breed?petTypeId={petTypeId}",
     *     operationId="getPetBreeds",
     *     summary="Get all pet breeds by petTypeId",
     *     tags={"PetBreed"},
     *     @OA\Parameter(
     *         name="petTypeId",
     *         description="ID of the pet type",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="items", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="_meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Missing petTypeId"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pet type not found"
     *     )
     * )
     */
    public function actionIndex($petTypeId)
    {
        if (empty($petTypeId)) {
            throw new \yii\web\BadRequestHttpException('petTypeId is required');
        }

        $query = PetBreed::find()
            ->select(['id', '@@name'])
            ->where(['pet_type_id' => $petTypeId])
            ->asArray();

        $dataProvider = new AdvActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10
            ],
            'sort' => [
                'defaultOrder' => [
                    '@@name' => SORT_ASC,
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

    /**
     * Displays a single PetBreed model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     *
     * @OA\Get(
     *     path="/api/pet-breed/{id}",
     *     summary="Get a single pet breed",
     *     tags={"PetBreed"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="PetBreed not found"
     *     )
     * )
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new PetBreed model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     *
     * @OA\Post(
     *     path="/api/pet-breed",
     *     summary="Create a new pet breed",
     *     tags={"PetBreed"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PetBreed")
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
        $model = new PetBreed();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing PetBreed model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     *
     * @OA\Put(
     *     path="/api/pet-breed/{id}",
     *     summary="Update a pet breed",
     *     tags={"PetBreed"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PetBreed")
     *     ),
     *     @OA\Response(
     *        response=201, description="Updated"
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

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing PetBreed model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     *
     * @OA\Delete(
     *     path="/api/pet-breed/{id}",
     *     operationId="deletePetBreed",
     *     summary="Delete a pet breed",
     *     tags={"PetBreed"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
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

        return $this->redirect(['index']);
    }

    /**
     * Finds the PetBreed model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return PetBreed the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PetBreed::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
