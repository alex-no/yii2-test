<?php

namespace app\components;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Yii;
use yii\web\UnauthorizedHttpException;

class JwtHelper
{
    private static string $secret = '';
    private static string $algo = 'HS256';
    private static int $ttl = 3600; // 1 hour

    public static function getSecret()
    {
        if (self::$secret === '') {
            self::$secret = param('JwtSecret');  // Получаем значение из конфигурации
        }
        return self::$secret;
    }

    public static function generateToken($user)
    {
        $issuedAt = time();
        $expire = $issuedAt + self::$ttl;

        return JWT::encode([
            'iss' => 'your-api',
            'aud' => 'your-api-client',
            'iat' => $issuedAt,
            'exp' => $expire,
            'uid' => $user->id,
        ], self::getSecret(), self::$algo);
    }

    public static function decodeToken($token)
    {
        return JWT::decode($token, new Key(self::getSecret(), self::$algo));
    }

    public static function getUserFromToken($token)
    {
        try {
            $payload = self::decodeToken($token);
            return \app\models\User::findOne($payload->uid);
        } catch (\Exception $e) {
            throw new UnauthorizedHttpException("Invalid or expired token");
        }
    }
}
