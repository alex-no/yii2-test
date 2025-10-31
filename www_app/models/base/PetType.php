<?php

namespace app\models\base;

use Yii;
use AlexNo\FieldLingo\Adapters\Yii2\LingoActiveRecord;
use AlexNo\FieldLingo\Adapters\Yii2\LingoActiveQuery;

/**
 * This is the base model class for table "pet_type".
 *
 * @property int $id
 * @property string|null $name_uk
 * @property string|null $name_en
 * @property string|null $name_ru
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class PetType extends LingoActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%pet_type}}';
    }

    /**
     * {@inheritdoc}
     */
    public static function find(): LingoActiveQuery
    {
        return new LingoActiveQuery(static::class);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name_uk', 'name_en', 'name_ru', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['created_at', 'updated_at'], 'safe'],
            [['name_uk', 'name_en', 'name_ru'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name_uk' => 'Name Uk',
            'name_en' => 'Name En',
            'name_ru' => 'Name Ru',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }


    // Relations
    /**
     * Gets query for [[PetBreeds]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPetBreeds()
    {
        return $this->hasMany(PetBreed::class, ['pet_type_id' => 'id']);
    }
    /**
     * Gets query for [[PetOwners]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPetOwners()
    {
        return $this->hasMany(PetOwner::class, ['pet_type_id' => 'id']);
    }

}
