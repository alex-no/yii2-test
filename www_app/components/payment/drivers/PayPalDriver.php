<?php
namespace app\components\payment\drivers;

use app\components\payment\PaymentInterface;

class PayPalDriver implements PaymentInterface
{
    public const NAME = 'PayPal';
    public const VERSION = '1.0.0';
    // public const PAYMENT_URL = 'https://www.paypal.com/cgi-bin/webscr';
    public const PAYMENT_URL = 'https://www.sandbox.paypal.com/cgi-bin/webscr';

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
            //'business'      => $this->clientId,
            'business'      => 'sb-yppsq43510514@business.example.com',
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
     * Handles PayPal IPN callback.
     * @param array $request
     * @return array|null
     */
    public function handleCallback(array $request): ?array
    {
        if (!isset($request['txn_id'])) {
            return null;
        }

        return [
            'transaction_id' => $request['txn_id'],
            'status'         => $request['payment_status'] ?? null,
            'amount'         => $request['mc_gross'] ?? null,
            'currency'       => $request['mc_currency'] ?? null,
            'order_id'       => $request['custom'] ?? null,
        ];
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
