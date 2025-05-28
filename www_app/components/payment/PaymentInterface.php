<?php
namespace app\components\payment;

interface PaymentInterface
{
    /**
     * Creates a payment request with the given parameters.
     * @param array $params
     * @return array
     */
    public function createPayment(array $params): array;

    /**
     * Handles the payment callback from the payment gateway.
     * This method should process the callback data,
     * verify the payment, and return the result.
     * @param array $request
     * @return array
     */
    public function handleCallback(array $request): array;

    /**
     * Verifies the signature of the payment data.
     * This method should check if the signature matches the expected value.
     * @param string $data
     * @param string $signature
     * @return bool
     */
    public function verifySignature(string $data, string $signature): bool;
}
