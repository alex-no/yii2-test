<?php

namespace app\models\base;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the base model class for table "language".
 *
 * @property string $code
 * @property string $short_name
 * @property string $full_name
 * @property int $is_enabled
 * @property int $order
 */
class Language extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'language';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_enabled'], 'default', 'value' => 1],
            [['code', 'short_name', 'full_name', 'order'], 'required'],
            [['is_enabled', 'order'], 'integer'],
            [['code'], 'string', 'max' => 2],
            [['short_name'], 'string', 'max' => 3],
            [['full_name'], 'string', 'max' => 32],
            [['code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'code' => 'Code',
            'short_name' => 'Short Name',
            'full_name' => 'Full Name',
            'is_enabled' => 'Is Enabled',
            'order' => 'Order',
        ];
    }


    // Relations
    /**
     * Gets query for [[Users]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['language_code' => 'code']);
    }

}
