<?php

namespace app\api\modules\v1\controllers;

use Yii;
use yii\web\NotFoundHttpException;
// use yii\filters\VerbFilter;
use app\api\components\ApiController;
use app\models\PetType;
use app\components\i18n\AdvActiveDataProvider;

/**
 * PetTypeController implements the CRUD actions for PetType model.
 *
 * @OA\Schema(
 *     schema="PetType",
 *     required={"name"},
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="description", type="string", nullable=true)
 * )
 */
class PetTypeController extends ApiController
{
    /**
     * @inheritDoc
     */
    // public function behaviors()
    // {
    //     $behaviors = parent::behaviors();
    //     $behaviors['verbs'] = [
    //         'class' => VerbFilter::class,
    //         'actions' => [
    //             'delete' => ['POST'],
    //         ],
    //     ];
    //     return $behaviors;
    // }

    /**
     * @OA\Get(
     *     path="/api/pet-types",
     *     summary="Get list of Pet Types",
     *     tags={"PetTypes"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example="1"),
     *                     @OA\Property(property="name", type="string", example="dog"),
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
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     )
     * )
     *
     * Lists all PetType models.
     */
    public function actionIndex()
    {
        $query = PetType::find()
            // ->select('id, @@name')
            ->select(['id', '@@name'])
            // ->select([
            //     "id" => "id",
            //     "name" => "@@name",
            // ])
            // ->orderBy(['@@name' => SORT_ASC])
            // ->orderBy('@@name DESC')
            ->asArray();

        $dataProvider = new AdvActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 5
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
     * @OA\Get(
     *     path="/api/pet-types/{id}",
     *     summary="Retrieve a specific resource by ID",
     *     tags={"PetTypes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the Pet Type to retrieve",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful retrieval of the resource",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="id",
     *                 type="integer",
     *                 example="1",
     *                 description="ID of the requested Pet Type"
     *             ),
     *             @OA\Property(
     *                 property="name_uk",
     *                 type="string",
     *                 example="собака",
     *                 description="Name of the requested Pet Type in Ukrainian"
     *             ),
     *             @OA\Property(
     *                 property="name_en",
     *                 type="string",
     *                 example="dog",
     *                 description="Name of the requested Pet Type in English"
     *             ),
     *             @OA\Property(
     *                 property="name_ru",
     *                 type="string",
     *                 example="собака",
     *                 description="Name of the requested Pet Type in Russian"
     *             ),
     *             @OA\Property(
     *                 property="updated_at",
     *                 type="datetime",
     *                 example="2025-03-12T20:08:04.566Z",
     *                 description="Date and time of the last update"
     *             ),
     *             @OA\Property(
     *                 property="created_at",
     *                 type="datetime",
     *                 example="2025-03-12T20:08:04.566Z",
     *                 description="Date and time of the creation"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource not found"
     *     )
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
     *     path="/api/pet-types",
     *     summary="Store a new Pet Type",
     *     description="Creates a new Pet Type and stores it in the database.",
     *     operationId="storePetTypes",
     *     tags={"PetTypes"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name_uk", "name_en", "name_ru"},
     *             @OA\Property(
     *                 property="name_uk",
     *                 type="string",
     *                 example="собака",
     *                 description="Name of the created Pet Type in Ukrainian"
     *             ),
     *             @OA\Property(
     *                 property="name_en",
     *                 type="string",
     *                 example="dog",
     *                 description="Name of the created Pet Type in English"
     *             ),
     *             @OA\Property(
     *                 property="name_ru",
     *                 type="string",
     *                 example="собака",
     *                 description="Name of the created Pet Type in Russian"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pet Type created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="id",
     *                 type="integer",
     *                 example="1",
     *                 description="ID of the created Pet Type"
     *             ),
     *             @OA\Property(
     *                 property="name_uk",
     *                 type="string",
     *                 example="собака",
     *                 description="Name of the created Pet Type in Ukrainian"
     *             ),
     *             @OA\Property(
     *                 property="name_en",
     *                 type="string",
     *                 example="dog",
     *                 description="Name of the created Pet Type in English"
     *             ),
     *             @OA\Property(
     *                 property="name_ru",
     *                 type="string",
     *                 example="собака",
     *                 description="Name of the created Pet Type in Russian"
     *             ),
     *             @OA\Property(
     *                 property="updated_at",
     *                 type="datetime",
     *                 example="2025-03-12T20:08:04.566Z",
     *                 description="Date and time of the last update"
     *             ),
     *             @OA\Property(
     *                 property="created_at",
     *                 type="datetime",
     *                 example="2025-03-12T20:08:04.566Z",
     *                 description="Date and time of the creation"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
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
     *     path="/api/pet-types/{id}",
     *     summary="Update an existing Pet Type",
     *     description="Updates the details of an existing Pet Type by its ID.",
     *     operationId="updatePetType",
     *     tags={"PetTypes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the Pet Type to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="name_uk",
     *                 type="string",
     *                 example="собака",
     *                 description="Name of the updated Pet Type in Ukrainian"
     *             ),
     *             @OA\Property(
     *                 property="name_en",
     *                 type="string",
     *                 example="dog",
     *                 description="Name of the updated Pet Type in English"
     *             ),
     *             @OA\Property(
     *                 property="name_ru",
     *                 type="string",
     *                 example="собака",
     *                 description="Name of the updated Pet Type in Russian"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Resource updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="id",
     *                 type="integer",
     *                 example="1",
     *                 description="ID of the updated Pet Type"
     *             ),
     *             @OA\Property(
     *                 property="name_uk",
     *                 type="string",
     *                 example="собака",
     *                 description="Name of the updated Pet Type in Ukrainian"
     *             ),
     *             @OA\Property(
     *                 property="name_en",
     *                 type="string",
     *                 example="dog",
     *                 description="Name of the updated Pet Type in English"
     *             ),
     *             @OA\Property(
     *                 property="name_ru",
     *                 type="string",
     *                 example="собака",
     *                 description="Name of the updated Pet Type in Russian"
     *             ),
     *             @OA\Property(
     *                 property="updated_at",
     *                 type="datetime",
     *                 example="2025-03-12T20:08:04.566Z",
     *                 description="Date and time of the last update"
     *             ),
     *             @OA\Property(
     *                 property="created_at",
     *                 type="datetime",
     *                 example="2025-03-12T20:08:04.566Z",
     *                 description="Date and time of the creation"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error",
     *                 type="string"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error",
     *                 type="string"
     *             )
     *         )
     *     )
     * )
     *
     * Updates an existing PetType model.
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
     * @OA\Delete(
     *     path="/api/pet-types/{id}",
     *     summary="Delete a Pet Type",
     *     description="Deletes a PetType by its ID",
     *     operationId="destroyPetType",
     *     tags={"PetTypes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the Pet Type to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pet Type deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Pet Type deleted successfully"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Resource not found"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Internal server error"
     *             )
     *         )
     *     )
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
