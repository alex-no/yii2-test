<?php
namespace app\api\modules\v1\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use app\models\User;
use yii\web\BadRequestHttpException;
use app\components\JwtHelper;

class AuthController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator']['formats']['application/json'] = Response::FORMAT_JSON;
        return $behaviors;
    }

    /**
     * @OA\Post(
     *     path="/auth/register",
     *     summary="User registration",
     *     description="Register a new user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "email", "password"},
     *             @OA\Property(property="username", type="string", example="johndoe"),
     *             @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *             @OA\Property(property="phone", type="string", example="+1234567890"),
     *             @OA\Property(property="password", type="string", example="securepassword")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User successfully registered",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Validation errors")
     *         )
     *     )
     * )
     */
    public function actionRegister()
    {
        $body = Yii::$app->request->bodyParams;

        $user = new User();
        $user->username = $body['username'] ?? null;
        $user->email = $body['email'] ?? null;
        $user->phone = $body['phone'] ?? null;
        $user->setPassword($body['password'] ?? '');
        $user->generateAuthData();
        $user->created_at = time();
        $user->updated_at = time();

        if ($user->save()) {
            return ['success' => true];
        }

        throw new BadRequestHttpException(json_encode($user->getErrors()));
    }

    /**
     * @OA\Post(
     *     path="/auth/login",
     *     summary="User login",
     *     description="Login a user and return access token",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "password"},
     *             @OA\Property(property="username", type="string", example="johndoe"),
     *             @OA\Property(property="password", type="string", example="securepassword")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Access token returned",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string", example="your_access_token_here")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid username or password")
     *         )
     *     )
     * )
     */
    public function actionLogin()
    {
        $body = Yii::$app->request->bodyParams;
        $user = User::findByUsername($body['username'] ?? '');

        if ($user && $user->validatePassword($body['password'] ?? '')) {
            $token = JwtHelper::generateToken($user);
            return ['access_token' => $token];
        }

        throw new BadRequestHttpException('Invalid username or password');
    }
}
