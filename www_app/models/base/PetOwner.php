<?php

namespace app\models\base;

use Yii;
use app\components\i18n\AdvActiveRecord;
use app\components\i18n\AdvActiveQuery;

/**
 * This is the base model class for table "pet_owner".
 *
 * @property int $id
 * @property int $user_id
 * @property int $pet_type_id
 * @property int|null $pet_breed_id
 * @property string|null $nickname_uk
 * @property string|null $nickname_en
 * @property string|null $nickname_ru
 * @property int|null $year_of_birth
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class PetOwner extends AdvActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%pet_owner}}';
    }

    /**
     * {@inheritdoc}
     */
    public static function find(): AdvActiveQuery
    {
        return new AdvActiveQuery(static::class);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pet_breed_id', 'nickname_uk', 'nickname_en', 'nickname_ru', 'year_of_birth', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['user_id', 'pet_type_id'], 'required'],
            [['user_id', 'pet_type_id', 'pet_breed_id', 'year_of_birth'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['nickname_uk', 'nickname_en', 'nickname_ru'], 'string', 'max' => 255],
            [['pet_breed_id'], 'exist', 'skipOnError' => true, 'targetClass' => PetBreed::class, 'targetAttribute' => ['pet_breed_id' => 'id']],
            [['pet_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => PetType::class, 'targetAttribute' => ['pet_type_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'pet_type_id' => Yii::t('app', 'Pet Type ID'),
            'pet_breed_id' => Yii::t('app', 'Pet Breed ID'),
            'nickname_uk' => Yii::t('app', 'Nickname Uk'),
            'nickname_en' => Yii::t('app', 'Nickname En'),
            'nickname_ru' => Yii::t('app', 'Nickname Ru'),
            'year_of_birth' => Yii::t('app', 'Year Of Birth'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }


    // Relations
    /**
     * Gets query for [[PetBreed]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPetBreed()
    {
        return $this->hasOne(PetBreed::class, ['id' => 'pet_breed_id']);
    }
    /**
     * Gets query for [[PetType]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPetType()
    {
        return $this->hasOne(PetType::class, ['id' => 'pet_type_id']);
    }
    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

}
