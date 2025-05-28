<?php
namespace app\components\payment\drivers;

use app\components\payment\PaymentInterface;

class LiqPayDriver implements PaymentInterface
{
    const NAME = 'LiqPay';
    const VERSION = '1.0.0';
    const PAYMENT_URL = 'https://www.liqpay.ua/api/3/checkout';
    //const PAYMENT_CALLBACK_URL = 'https://www.liqpay.ua/api/3/callback';

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
        $signature = base64_encode(sha1($this->privateKey . $json . $this->privateKey, true));

        return [
            'action'    => self::PAYMENT_URL,
            'data'      => $json,
            'signature' => $signature,
        ];
    }

    /**
     * Handles the payment callback from the payment gateway.
     * This method should process the callback data,
     * verify the payment, and return the result.
     * @param array $request
     * @return array
     */
    public function handleCallback(array $request): array
    {
        $data = json_decode(base64_decode($request['data']), true);
        return $data;
    }

    /**
     * Verifies the signature of the payment data.
     * This method should check if the signature matches the expected value.
     * @param string $data
     * @param string $signature
     * @return bool
     */
    public function verifySignature(string $data, string $signature): bool
    {
        $expected = base64_encode(sha1($this->privateKey . $data . $this->privateKey, true));
        return hash_equals($expected, $signature);
    }
}
