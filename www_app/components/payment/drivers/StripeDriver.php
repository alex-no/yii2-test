<?php
namespace app\components\payment\drivers;

use app\components\payment\PaymentInterface;
use app\models\Order;
use yii\web\BadRequestHttpException;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Webhook;

class StripeDriver implements PaymentInterface
{
    public const NAME = 'Stripe';
    public const VERSION = '1.0.0';
    public const PAYMENT_URL = 'https://api.stripe.com/v1/payment_intents';

    /**
     * StripeDriver constructor.
     * @param string $apiKey - Stripe API key
     * @param string $webhookSecret - Stripe webhook secret for signature verification
     * @param string $callbackUrl - Webhook URL for notifications
     * @param string $returnUrl - URL for redirect after success
     * @param string $cancelUrl - URL for redirect if user cancels
     */
    public function __construct(
        private string $apiKey,
        private string $webhookSecret,
        private string $callbackUrl,
        private string $returnUrl,
        private string $cancelUrl
    ) {
        Stripe::setApiKey($this->apiKey);
    }

    /**
     * Creates a Payment Intent for Stripe.
     * Returns data for initiating payment on the client side.
     *
     * @param array $params Payment parameters: amount, currency, description, order_id, etc.
     * @return array{
     *     action: string,         // API URL (returned for interface compatibility)
     *     method: 'POST'|'GET',   // Method (for interface compatibility)
     *     data: array<string, string> // Key-value pairs including client_secret
     * }
     */
    public function createPayment(array $params): array
    {
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $params['amount'] * 100, // Stripe expects amount in cents
                'currency' => strtolower($params['currency'] ?? 'USD'),
                'description' => $params['description'],
                'metadata' => [
                    'order_id' => $params['order_id'],
                ],
                'confirmation_method' => 'manual',
                'capture_method' => 'manual',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            return [
                'action' => self::PAYMENT_URL,
                'method' => 'POST',
                'data' => [
                    'client_secret' => $paymentIntent->client_secret,
                    'payment_intent_id' => $paymentIntent->id,
                    'return_url' => $this->returnUrl,
                    'cancel_url' => $this->cancelUrl,
                ],
            ];
        } catch (\Exception $e) {
            throw new BadRequestHttpException("Failed to create Stripe payment: " . $e->getMessage());
        }
    }

    /**
     * Handles Stripe webhook callback.
     * @param array $post
     * @return Order|null
     */
    public function handleCallback(array $post): ?Order
    {
        try {
            $payload = file_get_contents('php://input');
            $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

            $event = Webhook::constructEvent(
                $payload,
                $signature,
                $this->webhookSecret
            );

            if ($event->type === 'payment_intent.succeeded') {
                $paymentIntent = $event->data->object;
                $orderId = $paymentIntent->metadata->order_id ?? null;
                $status = $paymentIntent->status === 'succeeded' ? 'success' : 'pending';

                if (!$orderId) {
                    throw new BadRequestHttpException("Invalid webhook data: missing order_id.");
                }

                $order = Order::findOne(['order_id' => $orderId]);
                if (!$order) {
                    return null; // Order not found
                }

                $order->payment_status = $status;
                return $order;
            }

            return null;
        } catch (\Exception $e) {
            throw new BadRequestHttpException("Invalid webhook data: " . $e->getMessage());
        }
    }

    /**
     * Verifies the Stripe webhook signature.
     * @param string $data
     * @param string $signature
     * @return bool
     */
    public function verifySignature(string $data, string $signature): bool
    {
        try {
            Webhook::constructEvent($data, $signature, $this->webhookSecret);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
