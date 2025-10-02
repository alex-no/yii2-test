<?php
namespace app\components\payment;
use app\models\Order;

interface PaymentInterface
{
    /**
     * Returns a URL or HTML form that can be used to initiate payment.
     *
     * @param array $params Payment parameters: amount, currency, description, order_id, etc.
     * @return array{
     *     action: string,         // Form action URL
     *     method: 'POST'|'GET',   // Form method
     *     data: array<string, string> // Key-value pairs for form inputs
     * }
     */
    public function createPayment(array $params): array;

    /**
     * Handles the payment callback from the payment gateway.
     * This method should process the callback data,
     * verify the payment, and return the result.
     * @param array $post
     * @return array ['status' => string, 'order' => ?Order]
     */
    public function handleCallback(array $post): array;

    /**
     * Verifies the signature of the payment data.
     * This method should check if the signature matches the expected value.
     * @param string $data
     * @param string $signature
     * @return bool
     */
    public function verifySignature(string $data, string $signature): bool;
}
