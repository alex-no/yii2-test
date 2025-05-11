<?php

namespace app\api\modules\v1\controllers;

use Yii;
use app\api\components\ApiController;
use app\models\User;
use yii\web\NotFoundHttpException;

class UserController extends ApiController
{
    protected array $authOnly = ['profile', 'logout', 'view', 'update'];

    /**
     * @OA\Get(
     *     path="/api/user/profile",
     *     summary="Get user profile",
     *     description="Returns the profile of the authenticated user. You must include a valid token in the Authorization header.",
     *     tags={"User"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User profile data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="username", type="string", example="johndoe"),
     *             @OA\Property(property="email", type="string", example="johndoe@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Token is missing or invalid",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function actionProfile()
    {
        return Yii::$app->user->identity->toPublicArray(); // Assuming the User model has a method to return public attributes
    }

    /**
     * @OA\Post(
     *     path="/api/user/logout",
     *     summary="User logout",
     *     description="Logs out the authenticated user. You must include a valid token in the Authorization header.",
     *     tags={"User"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Logged out")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Token is missing or invalid",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function actionLogout()
    {
        // JWT cannot be "deleted", but a blacklist can be implemented
        return ['message' => 'Logged out (client must delete token)'];
    }

    /**
     * @OA\Get(
     *     path="/api/user/<id:\d+>",
     *     summary="Get user by ID",
     *     description="Returns a single user by their ID. You must include a valid token in the Authorization header.",
     *     operationId="actionView",
     *     security={{"bearerAuth": {}}},
     *     tags={"User"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user to retrieve",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="username", type="string", example="johndoe"),
     *             @OA\Property(property="email", type="string", example="alex@4n.com.ua"),
     *             @OA\Property(property="phone", type="string", example="+380123456789"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-01T12:00:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2023-10-01T12:00:00Z"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function actionView($id)
    {
        return $this->findModel($id);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $model;
    }

    protected function findModel($id)
    {
        if (($model = User::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
