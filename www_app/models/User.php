<?php

namespace app\models;

use Yii;
use app\models\base\User as UserBase;
use yii\web\IdentityInterface;

/**
 * Class User — extend your logic here.
 */
class User extends UserBase implements IdentityInterface
{

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id): ?self
    {
        return static::findOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        // return static::findOne(['access_token' => $token]);
        return null;
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername(string $username): ?self
    {
        return static::findOne(['username' => $username]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey(): string
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey): bool
    {
        return $this->auth_key === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * {@inheritdoc}
     */
    public function generateAuthData(): void
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
        $this->access_token = Yii::$app->security->generateRandomString(64);
    }

    /**
     * {@inheritdoc}
     */
    public function setPassword(string $password): void
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }
}
