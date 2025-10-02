<?php
namespace app\components\payment\drivers;

use Yii;
use app\components\payment\PaymentInterface;
use app\models\Order;
use yii\web\BadRequestHttpException;

class LiqPayDriver implements PaymentInterface
{
    public const NAME = 'LiqPay';
    public const VERSION = '1.0.1';
    public const PAYMENT_URL = 'https://www.liqpay.ua/api/3/checkout';
    // public const PAYMENT_CALLBACK_URL = 'https://www.liqpay.ua/api/3/callback';

    /**
     * LiqPayDriver constructor.
     * @param string $publicKey - The public key for LiqPay API.
     * @param string $privateKey - The private key for LiqPay API.
     * @param string $callbackUrl - The URL to which the payment response will be sent.
     */
    public function __construct(
        private string $publicKey,
        private string $privateKey,
        private string $callbackUrl,
        private string $resultUrl
    ) {}

    /**
     * Creates a LiqPay payment form data.
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
            'version'     => '3',
            'public_key'  => $this->publicKey,
            'action'      => 'pay',
            'amount'      => $params['amount'],
            'currency'    => $params['currency'] ?? 'UAH',
            'description' => $params['description'],
            'order_id'    => $params['order_id'],
            'server_url'  => $this->callbackUrl,
            'result_url'  => $this->resultUrl ?? null,
        ];

        $json = base64_encode(json_encode($data));
        $signature = $this->generateSignature($json);

        return [
            'action' => self::PAYMENT_URL,
            'method' => 'POST',
            'data'   => [
                'data'      => $json,
                'signature' => $signature,
            ],
        ];
    }

    /**
     * Collects callback data from Yii request for LiqPay.
     *
     * @return array
     * @throws BadRequestHttpException
     */
    public function getCallbackData(): array
    {
        $data = Yii::$app->request->post();
        if (empty($data['data']) || empty($data['signature'])) {
            throw new BadRequestHttpException("Missing LiqPay callback data or signature.");
        }
        return $data;
    }

    /**
     * Handles LiqPay payment callback.
     *
     * @param array $data
     * @return array ['status' => string, 'order' => ?Order]
     * @throws BadRequestHttpException
     */
    public function handleCallback(array $data): array
    {
        if (!$this->verifySignature($data['data'], $data['signature'])) {
            throw new BadRequestHttpException("Invalid LiqPay signature.");
        }

        $data = json_decode(base64_decode($data['data']), true);

        $orderId = $data['order_id'] ?? null;
        $status  = $data['status'] ?? null;

        if (!$orderId || !$status) {
            throw new BadRequestHttpException("Invalid LiqPay callback data.");
        }

        $order = Order::findOne(['order_id' => $orderId]);
        if (!$order) {
            return ['status' => 'not_found', 'order' => null];
        }

        $order->payment_status = $status;

        return ['status' => 'processed', 'order' => $order];
    }

    /**
     * Verifies the signature of the payment data.
     * This method should check if the signature matches the expected value.
     * @param string $json
     * @param string $signature
     * @return bool
     */
    public function verifySignature(string $json, string $signature): bool
    {
        return hash_equals($this->generateSignature($json), $signature);
    }

    /**
     * Generates LiqPay signature.
     *
     * @param string $json
     * @return string
     */
    protected function generateSignature(string $json): string
    {
        return base64_encode(sha1($this->privateKey . $json . $this->privateKey, true));
    }
}
