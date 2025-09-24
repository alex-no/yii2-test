<?php
namespace app\components\payment\drivers;

use app\components\payment\PaymentInterface;
use app\models\Order;
use yii\web\BadRequestHttpException;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

/**
 * Stripe payment driver implementing PaymentInterface.
 *
 * This driver uses Stripe Payment Intents API for processing payments.
 * It supports automatic payment methods and webhook handling for confirmation.
 */
class StripeDriver implements PaymentInterface
{
    public const NAME = 'Stripe';
    public const VERSION = '1.0.0';
    public const PAYMENT_URL = ''; // Stripe does not require a direct form action URL

    /**
     * @param string $apiKey Stripe API key
     * @param string $webhookSecret Stripe webhook signing secret for signature verification
     * @param string $callbackUrl Webhook URL where Stripe sends events
     * @param string $returnUrl URL for redirect after successful payment
     * @param string $cancelUrl URL for redirect if the user cancels
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
     * Creates a Stripe PaymentIntent and returns its client secret and metadata.
     *
     * @param array $params Payment parameters: amount, currency, description, order_id, etc.
     * @return array{
     *     action: string,
     *     method: 'POST'|'GET',
     *     data: array<string, string>
     * }
     * @throws BadRequestHttpException
     */
    public function createPayment(array $params): array
    {
        try {
            $paymentIntent = PaymentIntent::create([
                'amount'   => (int) round($params['amount'] * 100), // Stripe expects the amount in cents
                'currency' => strtolower($params['currency'] ?? 'USD'),
                'description' => $params['description'] ?? '',
                'metadata' => [
                    'order_id' => $params['order_id'] ?? '',
                ],
                'automatic_payment_methods' => ['enabled' => true],
            ]);

            return [
                'action' => self::PAYMENT_URL,
                'method' => 'POST', // kept for interface compatibility
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
     * Handles a Stripe webhook callback and updates order status if payment succeeded.
     *
     * @param array $post ['payload' => string, 'signature' => string]
     * @return Order|null The corresponding order if found, null otherwise
     * @throws BadRequestHttpException
     */
    public function handleCallback(array $post): ?Order
    {
        $payload = $post['payload'] ?? null;
        $signature = $post['signature'] ?? null;

        if (!$payload || !$signature) {
            throw new BadRequestHttpException("Missing webhook payload or signature.");
        }

        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                $this->webhookSecret
            );

            if ($event->type === 'payment_intent.succeeded') {
                $paymentIntent = $event->data->object;
                $orderId = $paymentIntent->metadata->order_id ?? null;

                if (!$orderId) {
                    throw new BadRequestHttpException("Invalid webhook data: missing order_id.");
                }

                $order = Order::findOne(['order_id' => $orderId]);
                if (!$order) {
                    return null; // Order not found
                }

                $order->payment_status = 'success';
                return $order;
            }

            return null;
        } catch (SignatureVerificationException $e) {
            throw new BadRequestHttpException("Invalid signature: " . $e->getMessage());
        } catch (\Exception $e) {
            throw new BadRequestHttpException("Webhook handling error: " . $e->getMessage());
        }
    }

    /**
     * Verifies the Stripe webhook signature.
     *
     * @param string $data Raw request payload
     * @param string $signature Signature from the Stripe-Signature header
     * @return bool True if signature is valid, false otherwise
     */
    public function verifySignature(string $data, string $signature): bool
    {
        try {
            Webhook::constructEvent($data, $signature, $this->webhookSecret);
            return true;
        } catch (SignatureVerificationException $e) {
            return false;
        }
    }
}
