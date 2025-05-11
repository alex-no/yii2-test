<?php

namespace app\api\modules\v1\controllers;

use app\models\PetOwner;
use app\api\components\ApiController;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use app\components\i18n\AdvActiveDataProvider;

/**
 * PetOwnerController implements the CRUD actions for PetOwner model.
 *
 * @OA\Schema(
 *     schema="PetOwner",
 *     required={"user_id", "pet_type_id", "pet_breed_id", "nickname"},
 *     @OA\Property(property="user_id", type="integer"),
 *     @OA\Property(property="pet_type_id", type="integer"),
 *     @OA\Property(property="pet_breed_id", type="integer"),
 *     @OA\Property(property="nickname", type="string"),
 *     @OA\Property(property="description", type="string", nullable=true)
 * )
 */
class PetOwnerController extends ApiController
{
    protected array $authOnly = [
        'index',
        'view',
        'create',
        'update',
        'delete',
    ];

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'view', 'create', 'update', 'delete'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view'],
                        'roles' => ['@'], // authenticated users
                    ],
                    // [
                    //     'allow' => true,
                    //     'actions' => ['create', 'update', 'delete'],
                    //     'roles' => ['petOwner.create', 'petOwner.update', 'petOwner.delete'],
                    // ],
                ],
            ],
        ]);
    }

    /**
     * Lists all PetOwner models.
     *
     * @return array
     *
     * @OA\Get(
     *     path="/api/pet-owners?userId={userId}&petTypeId={petTypeId}&petBreedId={petBreedId}",
     *     security={{"bearerAuth":{}}},
     *     operationId="getPetOwners",
     *     summary="Get all pet owners by petTypeId",
     *     tags={"PetOwner"},
     *     @OA\Parameter(
     *         name="userId",
     *         description="ID of the user",
     *         in="query",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="petTypeId",
     *         description="ID of the pet type",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="petBreedId",
     *         description="ID of the pet breed",
     *         in="query",
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
     *                     @OA\Property(property="user_id", type="integer"),
     *                     @OA\Property(property="pet_type_id", type="integer"),
     *                     @OA\Property(property="pet_breed_id", type="integer"),
     *                     @OA\Property(property="nickname", type="string", example="Sharick"),
     *                     @OA\Property(property="year_of_birth", type="integer", example="2020")
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
     *         description="At least one of userId, petTypeId or petBreedId is required"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pet type not found"
     *     )
     * )
     */
    public function actionIndex($userId = null, $petTypeId = null, $petBreedId = null)
    {
        $where = [];
        if (!empty($userId)) {
            $where['user_id'] = $userId;
        }
        if (!empty($petTypeId)) {
            $where['pet_type_id'] = $petTypeId;
        }
        if (!empty($petBreedId)) {
            $where['pet_breed_id'] = $petBreedId;
        }
        if (empty($where)) {
            throw new \yii\web\BadRequestHttpException('userId or petTypeId or petBreedId is required');
        }

        $query = PetOwner::find()
            ->select(['id', 'user_id', 'pet_type_id', 'pet_breed_id', 'nickname' => '@@nickname', 'year_of_birth'])
            ->where($where)
            ->asArray();

        $dataProvider = new AdvActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10
            ],
            'sort' => [
                'defaultOrder' => [
                    '@@nickname' => SORT_ASC,
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
     * Displays a single PetOwner model.
     *
     * @param int $id ID
     * @return array|PetOwner
     * @throws NotFoundHttpException if the model cannot be found
     *
     * @OA\Get(
     *     path="/api/pet-owners/{id}",
     *     security={{"bearerAuth":{}}},
     *     summary="Get a single pet and owner",
     *     tags={"PetOwner"},
     *     operationId="getPetOwnerById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the pet and owner",
     *         @OA\Schema(type="integer", example=11)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=11),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="pet_type_id", type="integer", example=1),
     *             @OA\Property(property="pet_breed_id", type="integer", example=1),
     *             @OA\Property(property="nickname_uk", type="string", example="Шарік"),
     *             @OA\Property(property="nickname_en", type="string", example="Sharick"),
     *             @OA\Property(property="nickname_ru", type="string", example="Шарик"),
     *             @OA\Property(property="year_of_birth", type="integer", example=2020),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-12 03:15:20"),
     *             @OA\Property(property="user_name", type="string", example="Petro"),
     *             @OA\Property(property="pet_type_name", type="string", example="dog"),
     *             @OA\Property(property="pet_breed_name", type="string", example="Chinese Crested Dog")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="PetOwner not found"
     *     )
     * )
     */
    public function actionView($id)
    {
        return $this->findModel($id);
    }

    /**
     * Creates a new PetOwner model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     *
     * @OA\Post(
     *     path="/api/pet-owners",
     *     security={{"bearerAuth":{}}},
     *     summary="Create a new pet and owner",
     *     tags={"PetOwner"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"user_id", "pet_breed_id"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="pet_breed_id", type="integer", example=1),
     *             @OA\Property(property="nickname_uk", type="string", example="Шарік"),
     *             @OA\Property(property="nickname_en", type="string", example="Sharick"),
     *             @OA\Property(property="nickname_ru", type="string", example="Шарик"),
     *             @OA\Property(property="year_of_birth", type="integer", example=2020)
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
        $model = new PetOwner();

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
     * Updates an existing PetOwner model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     *
     * @OA\Put(
     *     path="/api/pet-owners/{id}",
     *     security={{"bearerAuth":{}}},
     *     summary="Update a pet and owner",
     *     tags={"PetOwner"},
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
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="pet_breed_id", type="integer", example=1),
     *             @OA\Property(property="nickname_uk", type="string", example="Шарік"),
     *             @OA\Property(property="nickname_en", type="string", example="Sharick"),
     *             @OA\Property(property="nickname_ru", type="string", example="Шарик"),
     *             @OA\Property(property="year_of_birth", type="integer", example=2020)
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

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $model->toArray();
    }

    /**
     * Deletes an existing PetOwner model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     *
     * @OA\Delete(
     *     path="/api/pet-owners/{id}",
     *     security={{"bearerAuth":{}}},
     *     operationId="deletePetOwner",
     *     summary="Delete a pet owner",
     *     tags={"PetOwner"},
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
     * Finds the PetOwner model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return PetOwner the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PetOwner::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
