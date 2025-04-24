<?php

namespace app\models\base;

use Yii;
use app\components\i18n\AdvActiveRecord;

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
            'id' => 'ID',
            'user_id' => 'User ID',
            'pet_type_id' => 'Pet Type ID',
            'pet_breed_id' => 'Pet Breed ID',
            'nickname_uk' => 'Nickname Uk',
            'nickname_en' => 'Nickname En',
            'nickname_ru' => 'Nickname Ru',
            'year_of_birth' => 'Year Of Birth',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
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
