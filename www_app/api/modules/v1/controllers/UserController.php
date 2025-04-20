<?php

namespace app\api\modules\v1\controllers;

use yii\rest\Controller;
use yii\filters\auth\HttpBearerAuth;
use yii\web\Response;
use app\components\JwtAuth;
use Yii;

class UserController extends Controller
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            //'class' => HttpBearerAuth::class,
            'class' => JwtAuth::class,
        ];

        $behaviors['contentNegotiator']['formats']['application/json'] = Response::FORMAT_JSON;

        return $behaviors;
    }

    /**
     * @OA\Get(
     *     path="/user/profile",
     *     summary="Get user profile",
     *     description="Returns the profile of the authenticated user",
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
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function actionProfile()
    {
        return Yii::$app->user->identity;
    }

    /**
     * @OA\Post(
     *     path="/user/logout",
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
