<?php

namespace app\models\base;

use Yii;
use app\components\i18n\AdvActiveRecord;
use app\components\i18n\AdvActiveQuery;

/**
 * This is the base model class for table "development_plan".
 *
 * @property int $id
 * @property string $status
 * @property string $feature_en
 * @property string $feature_uk
 * @property string $feature_ru
 * @property string $technology_en
 * @property string $technology_uk
 * @property string $technology_ru
 * @property string|null $result_en
 * @property string|null $result_uk
 * @property string|null $result_ru
 * @property int $created_at
 * @property int $updated_at
 */
class DevelopmentPlan extends AdvActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%development_plan}}';
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
            [['result_en', 'result_uk', 'result_ru'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 'pending'],
            [['status', 'result_en', 'result_uk', 'result_ru'], 'string'],
            [['feature_en', 'feature_uk', 'feature_ru', 'technology_en', 'technology_uk', 'technology_ru', 'created_at', 'updated_at'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['feature_en', 'feature_uk', 'feature_ru'], 'string', 'max' => 255],
            [['technology_en', 'technology_uk', 'technology_ru'], 'string', 'max' => 512],
            // ['status', 'in', 'range' => array_keys(self::optsStatus())],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status' => 'Status',
            'feature_en' => 'Feature En',
            'feature_uk' => 'Feature Uk',
            'feature_ru' => 'Feature Ru',
            'technology_en' => 'Technology En',
            'technology_uk' => 'Technology Uk',
            'technology_ru' => 'Technology Ru',
            'result_en' => 'Result En',
            'result_uk' => 'Result Uk',
            'result_ru' => 'Result Ru',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}
