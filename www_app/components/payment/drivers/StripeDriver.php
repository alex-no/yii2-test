<?php
namespace app\components\payment\drivers;

use Yii;
use app\components\payment\PaymentInterface;
use app\models\Order;
use yii\web\BadRequestHttpException;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;

/**
 * Stripe payment driver implementing PaymentInterface.
 *
 * This driver uses Stripe Checkout Session API for processing payments.
 * It supports automatic webhook handling for confirmation.
 */
class StripeDriver implements PaymentInterface
{
    public const NAME = 'Stripe';
    public const VERSION = '1.0.0';
    public const PAYMENT_URL = ''; // Stripe does not use direct form submit

    /**
     * StripeDriver constructor.
     *
     * @param string $apiKey Stripe API key
     * @param string $webhookSecret Stripe webhook signing secret
     * @param string $callbackUrl Webhook URL for Stripe
     * @param string $returnUrl Redirect URL after successful payment
     * @param string $cancelUrl Redirect URL after the user cancels
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
     * Creates a Stripe Checkout Session and returns redirect information.
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
        $amount      = $params['amount'] ?? null;
        $currency    = strtolower($params['currency'] ?? 'USD');
        $description = $params['description'] ?? 'Payment';
        $orderId     = $params['order_id'] ?? null;

        if (!$amount || !$orderId) {
            throw new BadRequestHttpException("Missing required parameters: amount or order_id.");
        }

        try {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'mode' => 'payment',
                'line_items' => [[
                    'price_data' => [
                        'currency'     => $currency,
                        'product_data' => ['name' => $description],
                        'unit_amount'  => (int) round($amount * 100),
                    ],
                    'quantity' => 1,
                ]],
                'metadata' => [
                    'order_id' => (string)$orderId,
                ],
                'success_url' => $this->returnUrl . '?orderId=' . $orderId,
                'cancel_url'  => $this->cancelUrl . '?orderId=' . $orderId,
            ]);

            return [
                'action' => $session->url,  // URL for redirect to Stripe Checkout
                'method' => 'REDIRECT',
                'data'   => [],
            ];
        } catch (\Exception $e) {
            throw new BadRequestHttpException("Failed to create Stripe checkout session: " . $e->getMessage());
        }
    }

    /**
     * Collects callback data from Yii request for Stripe webhook.
     *
     * @return array ['payload' => string, 'signature' => string]
     * @throws BadRequestHttpException
     */
    public function getCallbackData(): array
    {
        $payload   = Yii::$app->request->rawBody;
        $signature = Yii::$app->request->headers->get('Stripe-Signature');

        if (empty($payload) || empty($signature)) {
            throw new BadRequestHttpException("Invalid Stripe callback: missing payload or signature.");
        }

        return [
            'payload'   => $payload,
            'signature' => $signature,
        ];
    }

    /**
     * Handles a Stripe webhook callback and updates order status.
     *
     * @param array $data Must contain ['payload' => string, 'signature' => string]
     * @return array The corresponding order if found, null otherwise
     * @throws BadRequestHttpException
     */
    public function handleCallback(array $data): array
    {
        $payload   = $data['payload']   ?? null;
        $signature = $data['signature'] ?? null;

        if (!$payload || !$signature) {
            throw new BadRequestHttpException("Missing webhook payload or signature.");
        }

        try {
            $event = Webhook::constructEvent($payload, $signature, $this->webhookSecret);

            switch ($event->type) {
                case 'checkout.session.completed':
                    $orderId = $this->getOrderId($event);
                    break;

                case 'payment_intent.succeeded':
                    $orderId = $this->getOrderId($event);
                    if (!$orderId) {
                        Yii::info("PaymentIntent succeeded, but no order_id in metadata - possibly not from Checkout or test event.");
                        return ['status' => 'ignored'];
                    }
                    break;

                default:
                    return ['status' => 'ignored'];
            }

            $order = Order::findOne(['order_id' => $orderId]);
            if (!$order) {
                Yii::warning("Order with ID {$orderId} not found for Stripe webhook.");
                return ['status' => 'not_found'];
            }

            $order->payment_status = 'success';
            return ['status' => 'processed', 'order' => $order];

        } catch (SignatureVerificationException $e) {
            throw new BadRequestHttpException("Invalid signature: " . $e->getMessage());
        } catch (\Throwable $e) {
            throw new BadRequestHttpException("Webhook handling error: " . $e->getMessage());
        }
    }

    /**
     * Extracts order ID from the Stripe event object.
     * @param \Stripe\Event $event
     * @return string|null
     */
    public function getOrderId($event): ?string
    {
        $object = $event->data->object; // PaymentIntent
        return $object->metadata->order_id ?? null;
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
