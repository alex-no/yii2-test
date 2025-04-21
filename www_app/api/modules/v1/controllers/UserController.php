<?php

namespace app\api\modules\v1\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use app\components\JwtAuth;

class UserController extends Controller
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => JwtAuth::class,
        ];

        $behaviors['contentNegotiator']['formats']['application/json'] = Response::FORMAT_JSON;

        return $behaviors;
    }

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
     *     description="Logs out the authenticated user",
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
     * )
     */
    public function actionLogout()
    {
        // JWT cannot be "deleted", but a blacklist can be implemented
        return ['message' => 'Logged out (client must delete token)'];
    }

}
