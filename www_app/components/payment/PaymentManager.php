<?php
namespace app\components\payment;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

class PaymentManager extends Component
{
    /**
     * Returns the payment driver instance.
     * This method provides access to the payment driver that has been initialized.
     * @param string $driverName
     * @return PaymentInterface The payment driver instance.
     *
     * @throws yii\base\InvalidConfigException
     */
    public function getDriver($driverName): PaymentInterface
    {
        $drivers = Yii::$app->params['payment.drivers'] ?? [];

        if (!$driverName || !isset($drivers[$driverName])) {
            throw new InvalidConfigException("Payment driver '$driverName' not configured.");
        }

        $driverClass = $drivers[$driverName]['class'];
        $driverConfig = $drivers[$driverName]['config'] ?? [];
        $driver = new $driverClass(...$driverConfig);
        return $driver;
    }
}
