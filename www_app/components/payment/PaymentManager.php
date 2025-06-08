<?php
namespace app\components\payment;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

class PaymentManager extends Component
{
    /**
     * @var PaymentInterface The payment driver instance.
     */
    private PaymentInterface $driver;

    /**
     * PaymentManager constructor.
     * Initializes the payment manager with the configured driver.
     * @param array $config the configuration for the payment manager.
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->init();
    }

    /**
     * Returns the payment driver instance.
     * This method provides access to the payment driver that has been initialized.
     * @return PaymentInterface The payment driver instance.
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
