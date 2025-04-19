<?php

namespace app\models\base;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the base model class for table "user".
 *
 * @property int $id
 * @property string|null $language_code
 * @property string $name
 * @property string $email
 * @property string|null $email_verified_at
 * @property string $password
 * @property string $auth_key
 * @property string|null $phone
 * @property string|null $remember_token
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class User extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['language_code', 'email_verified_at', 'phone', 'remember_token', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['name', 'email', 'password', 'auth_key'], 'required'],
            [['email_verified_at', 'created_at', 'updated_at'], 'safe'],
            [['language_code'], 'string', 'max' => 2],
            [['name', 'email', 'password'], 'string', 'max' => 191],
            [['auth_key'], 'string', 'max' => 32],
            [['phone'], 'string', 'max' => 16],
            [['remember_token'], 'string', 'max' => 100],
            [['email'], 'unique'],
            [['language_code'], 'exist', 'skipOnError' => true, 'targetClass' => Language::class, 'targetAttribute' => ['language_code' => 'code']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'language_code' => 'Language Code',
            'name' => 'Name',
            'email' => 'Email',
            'email_verified_at' => 'Email Verified At',
            'password' => 'Password',
            'auth_key' => 'Auth Key',
            'phone' => 'Phone',
            'remember_token' => 'Remember Token',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }


    // Relations
    /**
     * Gets query for [[LanguageCode]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLanguageCode()
    {
        return $this->hasOne(Language::class, ['code' => 'language_code']);
    }

}
