<?php

namespace app\models\base;

use Yii;
use app\components\i18n\AdvActiveRecord;
use app\components\i18n\AdvActiveQuery;

/**
 * This is the base model class for table "order".
 *
 * @property int $id
 * @property int $user_id
 * @property string $order_id
 * @property float $amount
 * @property string $currency
 * @property string $payment_status
 * @property string|null $description
 * @property string|null $paid_at
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Order extends AdvActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order}}';
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
            [['description', 'paid_at', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['currency'], 'default', 'value' => 'UAH'],
            [['payment_status'], 'default', 'value' => 'pending'],
            [['user_id', 'order_id', 'amount'], 'required'],
            [['user_id'], 'integer'],
            [['amount'], 'number'],
            [['payment_status'], 'string'],
            [['paid_at', 'created_at', 'updated_at'], 'safe'],
            [['order_id'], 'string', 'max' => 64],
            [['currency'], 'string', 'max' => 3],
            [['description'], 'string', 'max' => 255],
            //['payment_status', 'in', 'range' => array_keys(self::optsPaymentStatus())],
            [['order_id'], 'unique'],
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
            'order_id' => Yii::t('app', 'Order ID'),
            'amount' => Yii::t('app', 'Amount'),
            'currency' => Yii::t('app', 'Currency'),
            'payment_status' => Yii::t('app', 'Payment Status'),
            'description' => Yii::t('app', 'Description'),
            'paid_at' => Yii::t('app', 'Paid At'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }


    // Relations
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
