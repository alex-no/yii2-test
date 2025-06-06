<?php
namespace app\components\payment\drivers;

use app\components\payment\PaymentInterface;

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
     * Creates a payment request with the given parameters.
     * @param array $params
     * @return array
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

        return [
            'action'    => self::PAYMENT_URL,
            'data'      => $json,
            'signature' => $this->generateSignature($json),
        ];
    }

    /**
     * Handles the payment callback from the payment gateway.
     * This method should process the callback data,
     * verify the payment, and return the result.
     * @param array $request
     * @return array|null
     */
    public function handleCallback(array $request): ?array
    {
        if (!isset($request['data'])) {
            return null;
        }

        return json_decode(base64_decode($request['data']), true);
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
