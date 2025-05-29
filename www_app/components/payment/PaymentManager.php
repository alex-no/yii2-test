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
     * Initializes the payment manager by loading the configured payment driver.
     * This method checks the application parameters for the payment driver name
     * and retrieves the corresponding driver class and configuration.
     * @throws InvalidConfigException if the payment driver is not configured properly.
     */
    public function init(): void
    {
        parent::init();

        $driverName = Yii::$app->params['payment.driver'] ?? null;
        $drivers = Yii::$app->params['payment.drivers'] ?? [];

        if (!$driverName || !isset($drivers[$driverName])) {
            throw new InvalidConfigException("Payment driver '$driverName' not configured.");
        }

        $driverClass = $drivers[$driverName]['class'];
        $driverConfig = $drivers[$driverName]['config'] ?? [];
        $this->driver = new $driverClass(...$driverConfig);
    }

    /**
     * Returns the payment driver instance.
     * This method provides access to the payment driver that has been initialized.
     * @return PaymentInterface The payment driver instance.
     */
    public function getDriver(): PaymentInterface
    {
        return $this->driver;
    }
}
