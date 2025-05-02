<?php
namespace app\api\modules\v1\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use app\models\User;
use yii\web\BadRequestHttpException;
use app\components\JwtHelper;
use yii\helpers\Url;
use app\common\services\EmailService;

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
     *     path="/api/auth/register",
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

        $user->email_verified_at = null; // Not confirmed yet
        $token = $user->generateEmailVerificationToken();
        $user->remember_token = $token;

        if ($user->save()) {
            // send email confirmation
            $userEmail =  $user->email;
            $userName = $user->username;
            $confirmUrl = Url::to(['/api/auth/confirm-email', 'email' => $userEmail], true);

            $service = new EmailService();
            $sent = $service->sendConfirmation($userEmail, $userName, $confirmUrl);

            return $sent ?
                ['success' => true,  'message' => 'Confirmation email sent'] :
                ['success' => false, 'message' => 'Failed to send confirmation email'];
            // return $user->toPublicArray();
        }

        throw new BadRequestHttpException(json_encode($user->getErrors()));
    }

    /**
     * @OA\Get(
     *     path="/api/auth/confirm-email/{token}",
     *     summary="Confirm user email",
     *     tags={"Auth"},
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         required=true,
     *         description="Confirm user email token",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email confirmed"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid or expired token"
     *     )
     * )
     */
    public function actionConfirmEmail($token)
    {
        $user = User::find()->where(['remember_token' => $token])->one();

        if (!$user || !$user->isEmailVerificationTokenValid($token)) {
            throw new BadRequestHttpException('Invalid or expired token.');
        }

        $user->email_verified_at = date('Y-m-d H:i:s');
        $user->remember_token = null;

        if ($user->save(false)) {
            return ['success' => true, 'message' => 'Email confirmed'];
        }

        throw new BadRequestHttpException('Failed to confirm email.');
    }

    /**
     * @OA\Post(
     *     path="/api/auth/login",
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
        if ($user && $user->validatePassword($body['password'] ?? '')) {
            if (!$user->email_verified_at) {
                throw new BadRequestHttpException('Please verify your email first.');
            }

            $token = JwtHelper::generateToken($user);
            return ['access_token' => $token];
        }

        throw new BadRequestHttpException('Invalid username or password');
    }
}
