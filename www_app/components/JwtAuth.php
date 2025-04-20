<?php

namespace app\components;
// JSON Web Token (JWT) authentication component
// This is a simple example. In production, use a library like "firebase/php-jwt" for JWT handling.
// xxxxx.yyyyy.zzzzz - where xxxxx is the header, yyyyy is the payload, and zzzzz is the signature (https://jwt.io/)
use yii\filters\auth\AuthMethod;
use yii\web\UnauthorizedHttpException;
use Yii;

class JwtAuth extends AuthMethod
{
    public function authenticate($user, $request, $response)
    {
        $authHeader = $request->getHeaders()->get('Authorization');
        if ($authHeader && preg_match('/^Bearer\s+(.*?)$/', $authHeader, $matches)) {
            $token = $matches[1];
            $identity = JwtHelper::getUserFromToken($token);
            if ($identity) {
                return $identity;
            }
        }

        throw new UnauthorizedHttpException('Invalid or missing JWT token');
    }
    
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
