<?php

namespace app\api\modules\v1\controllers;

use app\models\PetBreed;
use app\api\components\ApiController;
use yii\web\NotFoundHttpException;
use app\components\i18n\AdvActiveDataProvider;

/**
 * PetBreedController implements the CRUD actions for PetBreed model.
 *
 * @OA\Schema(
 *     schema="PetBreed",
 *     @OA\Property(property="pet_type_id", type="integer"),
 *     required={"name", "pet_type_id"},
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="description", type="string", nullable=true)
 * )
 */
class PetBreedController extends ApiController
{
    /**
     * Lists all PetBreed models.
     *
     * @return array
     *
     * @OA\Get(
     *     path="/api/pet-breeds?petTypeId={petTypeId}",
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
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=11),
     *                     @OA\Property(property="name", type="string", example="Chinese Crested Dog")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="_meta",
     *                 type="object",
     *                 @OA\Property(property="totalCount", type="integer", example=16),
     *                 @OA\Property(property="pageCount", type="integer", example=2),
     *                 @OA\Property(property="currentPage", type="integer", example=2),
     *                 @OA\Property(property="perPage", type="integer", example=10)
     *             )
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
    public function actionIndex($petTypeId = null)
    {
        if (empty($petTypeId)) {
            throw new \yii\web\BadRequestHttpException('petTypeId is required');
        }

        $query = PetBreed::find()
            ->select(['id', 'name' => '@@name'])
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
     *
     * @param int $id ID
     * @return array|PetBreed
     * @throws NotFoundHttpException if the model cannot be found
     *
     * @OA\Get(
     *     path="/api/pet-breeds/{id}",
     *     summary="Get a single pet breed",
     *     tags={"PetBreed"},
     *     operationId="getPetBreedById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the pet breed",
     *         @OA\Schema(type="integer", example=11)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=11),
     *             @OA\Property(property="pet_type_id", type="integer", example=1),
     *             @OA\Property(property="name_uk", type="string", example="Китайський чубатий собака"),
     *             @OA\Property(property="name_en", type="string", example="Chinese Crested Dog"),
     *             @OA\Property(property="name_ru", type="string", example="Китайская хохлатая собака"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-12 03:15:20"),
     *             @OA\Property(property="pet_type_name", type="string", example="dog")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="PetBreed not found"
     *     )
     * )
     */
    public function actionView($id)
    {
        return $this->findModel($id);
    }

    /**
     * Creates a new PetBreed model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     *
     * @OA\Post(
     *     path="/api/pet-breeds",
     *     summary="Create a new pet breed",
     *     tags={"PetBreed"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"pet_type_id"},
     *             @OA\Property(property="pet_type_id", type="integer", example=1),
     *             @OA\Property(property="name_uk", type="string", example="Китайський чубатий собака"),
     *             @OA\Property(property="name_en", type="string", example="Chinese Crested Dog"),
     *             @OA\Property(property="name_ru", type="string", example="Китайская хохлатая собака")
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
        $model = new PetBreed();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $model->toArray();
    }

    /**
     * Updates an existing PetBreed model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     *
     * @OA\Put(
     *     path="/api/pet-breeds/{id}",
     *     summary="Update a pet breed",
     *     tags={"PetBreed"},
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
     *             @OA\Property(property="pet_type_id", type="integer", example=1),
     *             @OA\Property(property="name_uk", type="string", example="Китайський чубатий собака"),
     *             @OA\Property(property="name_en", type="string", example="Chinese Crested Dog"),
     *             @OA\Property(property="name_ru", type="string", example="Китайская хохлатая собака")
     *         )
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

        if ($this->request->isPut && $model->load($this->request->post(), '') && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $model->toArray();
    }

    /**
     * Deletes an existing PetBreed model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     *
     * @OA\Delete(
     *     path="/api/pet-breeds/{id}",
     *     operationId="deletePetBreed",
     *     summary="Delete a pet breed",
     *     tags={"PetBreed"},
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
