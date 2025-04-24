<?php

namespace app\models\base;

use Yii;
use app\components\i18n\AdvActiveRecord;

/**
 * This is the base model class for table "pet_breed".
 *
 * @property int $id
 * @property int $pet_type_id
 * @property string|null $name_uk
 * @property string|null $name_en
 * @property string|null $name_ru
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class PetBreed extends AdvActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%pet_breed}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name_uk', 'name_en', 'name_ru', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['pet_type_id'], 'required'],
            [['pet_type_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name_uk', 'name_en', 'name_ru'], 'string', 'max' => 255],
            [['pet_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => PetType::class, 'targetAttribute' => ['pet_type_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pet_type_id' => 'Pet Type ID',
            'name_uk' => 'Name Uk',
            'name_en' => 'Name En',
            'name_ru' => 'Name Ru',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }


    // Relations
    /**
     * Gets query for [[PetOwners]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPetOwners()
    {
        return $this->hasMany(PetOwner::class, ['pet_breed_id' => 'id']);
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

}
