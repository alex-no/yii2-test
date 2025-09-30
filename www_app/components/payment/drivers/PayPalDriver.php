<?php
namespace app\components\payment\drivers;

use Yii;
use app\components\payment\PaymentInterface;
use app\models\Order;
use yii\web\BadRequestHttpException;

class PayPalDriver implements PaymentInterface
{
    public const NAME = 'PayPal';
    public const VERSION = '1.0.0';
    // public const PAYMENT_URL = 'https://www.paypal.com/cgi-bin/webscr';
    public const PAYMENT_URL = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
    public const STATUS_MAP = [
        'completed' => 'success',
        'pending' => 'pending',
        'failed' => 'fail',
        'denied' => 'cancel',
        'refunded' => 'refund',
        'reversed' => 'reverse',
        'canceled_reversal' => 'cancel',
        'processed' => 'success',
        'voided' => 'cancel',
    ];

    /**
     * PayPalDriver constructor.
     * @param string $clientId - PayPal Client ID
     * @param string $secret - PayPal Secret
     * @param string $callbackUrl - Webhook URL for notifications
     * @param string $returnUrl - URL for redirect after success
     * @param string $cancelUrl - URL for redirect if user cancels
     */
    public function __construct(
        private string $clientId,
        private string $secret,
        private string $callbackUrl,
        private string $returnUrl,
        private string $cancelUrl
    ) {}

    /**
     * Creates a PayPal payment form data.
     * Returns a URL or HTML form that can be used to initiate payment.
     *
     * @param array $params Payment parameters: amount, currency, description, order_id, etc.
     * @return array{
     *     action: string,         // Form action URL
     *     method: 'POST'|'GET',   // Form method
     *     data: array<string, string> // Key-value pairs for form inputs
     * }
     */
    public function createPayment(array $params): array
    {
        $data = [
            'cmd'           => '_xclick',
            'business'      => $this->clientId,
            'item_name'     => $params['description'],
            'amount'        => $params['amount'],
            'currency_code' => $params['currency'] ?? 'USD',
            'notify_url'    => $this->callbackUrl,
            'return'        => $this->returnUrl,
            'cancel_return' => $this->cancelUrl,
            'custom'        => $params['order_id'] ?? '',
        ];

        return [
            'action' => self::PAYMENT_URL,
            'method' => 'POST',
            'data'   => $data,
        ];
    }

    /**
     * Collects callback data from Yii request for PayPal IPN.
     *
     * @return array
     * @throws BadRequestHttpException
     */
    public function getCallbackData(): array
    {
        $data = Yii::$app->request->post();
        if (empty($data)) {
            throw new BadRequestHttpException("Empty PayPal callback data.");
        }
        return $data;
    }

    /**
     * Handles PayPal IPN callback.
     *
     * @param array $data
     * @return array ['status' => string, 'order' => ?Order]
     * @throws BadRequestHttpException
     */
    public function handleCallback(array $data): array
    {
        if (!isset($data['txn_id'])) {
            return ['status' => 'ignored', 'order' => null];
        }

        $orderId = $data['custom'] ?? null;
        $status  = strtolower($data['payment_status'] ?? '');

        if (!$orderId || !$status) {
            throw new BadRequestHttpException("Invalid PayPal callback data.");
        }

        $order = Order::findOne(['order_id' => $orderId]);
        if (!$order) {
            return ['status' => 'not_found', 'order' => null];
        }

        $order->payment_status = self::STATUS_MAP[$status] ?? 'unknown';

        return ['status' => 'processed', 'order' => $order];
    }

    /**
     * Verifies the IPN signature (simulated here).
     * Real implementation should call back to PayPal to verify IPN.
     * @param string $data
     * @param string $signature
     * @return bool
     */
    public function verifySignature(string $data, string $signature): bool
    {
        // Stub: PayPal IPN verification should be done via HTTP POST to PayPal
        return true;
    }
}
