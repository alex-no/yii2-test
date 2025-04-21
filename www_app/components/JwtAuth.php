<?php

namespace app\components;
// JSON Web Token (JWT) authentication component
// This is a simple example. In production, use a library like "firebase/php-jwt" for JWT handling.
// xxxxx.yyyyy.zzzzz - where xxxxx is the header, yyyyy is the payload, and zzzzz is the signature (https://jwt.io/)
use Yii;
use yii\filters\auth\AuthMethod;
use yii\web\UnauthorizedHttpException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use app\models\User;

class JwtAuth extends AuthMethod
{
    public $authHeader = 'Authorization'; // Header for the token
    public $tokenPrefix = 'Bearer'; // Prefix for the token (e.g., Bearer <token>)

    /**
    * Authentication using JWT.
     *
     * @param \yii\web\User $user
     * @param \yii\web\Request $request
     * @param \yii\web\Response $response
     * @return \yii\web\IdentityInterface|null
     * @throws UnauthorizedHttpException
     */
    public function authenticate($user, $request, $response)
    {
        $header = $request->getHeaders()->get($this->authHeader);

        if ($header) {
            // Check if the header starts with 'Bearer'
            if (preg_match('/^' . $this->tokenPrefix . '\s+(.*?)$/i', $header, $matches)) {
                $token = $matches[1]; // Extract the token itself

                try {
                    // Decode the token using the JWT library (e.g., Firebase JWT)

                    //$headers = ['HS256']; // Specify the algorithm used for signing
                    $decoded = JWT::decode($token, new Key(Yii::$app->params['JwtSecret'], 'HS256'));
                    // Find the user by ID from the token
                    $identity = User::findIdentity($decoded->uid); // Assuming 'uid' is the user ID in the token payload

                    if ($identity) {
                        app()->user->setIdentity($identity);
                        return $identity;  // Return the user
                    }
                } catch (ExpiredException $e) {
                    throw new UnauthorizedHttpException('Token has expired.');
                } catch (\Exception $e) {
                    throw new UnauthorizedHttpException('Invalid or expired token.');
                }
            }
        }

        throw new UnauthorizedHttpException('Missing or invalid Authorization header.');
    }
    // public function authenticate($user, $request, $response)
    // {
    //     $authHeader = $request->getHeaders()->get('Authorization');
    //     if ($authHeader && preg_match('/^Bearer\s+(.*?)$/', $authHeader, $matches)) {
    //         $token = $matches[1];
    //         $identity = JwtHelper::getUserFromToken($token);
    //         if ($identity) {
    //             return $identity;
    //         }
    //     }

    //     throw new UnauthorizedHttpException('Invalid or missing JWT token');
    // }

    public function encode(array $payload, string $key): string
    {
        // Example of simple JWT encoding (use a library for production)
        return base64_encode(json_encode($payload)) . '.' . hash_hmac('sha256', json_encode($payload), $key);
    }

    public function decode(string $token, string $key): array
    {
        // Example of simple verification (DO NOT use in production!)
        [$payloadEncoded, $signature] = explode('.', $token);
        $payload = json_decode(base64_decode($payloadEncoded), true);
        $expectedSig = hash_hmac('sha256', json_encode($payload), $key);
        if ($expectedSig !== $signature) {
            throw new \Exception('Invalid signature');
        }
        return $payload;
    }
}
