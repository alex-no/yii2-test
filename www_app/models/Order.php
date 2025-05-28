<?php

namespace app\models;

use app\models\base\Order as OrderBase;

/**
 * Class Order — extend your logic here.
 */
class Order extends OrderBase
{
    /**
     * Get options for payment_status dropdown.
     * @return array<string, string>
     */
    public static function optsPaymentStatus(): array
    {
        return [
            'pending' => 'Pending',
            'success' => 'Success',
            'fail' => 'Fail',
            'cancel' => 'Cancel',
            'refund' => 'Refund',
            'expired' => 'Expired',
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            ['payment_status', 'in', 'range' => array_keys(self::optsPaymentStatus())],
        ]);
    }
}
