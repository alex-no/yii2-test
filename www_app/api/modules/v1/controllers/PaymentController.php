<?php
namespace app\api\modules\v1\controllers;

use Yii;
use app\api\components\ApiController;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;
use app\models\Order;

/**
 * PaymentController class for working with Payment models.
 *
 * @OA\Tag(
 *     name="Payment",
 *     description="API for working with payment models, including creating payments, handling callbacks, and verifying signatures."
 * )
 */
class PaymentController extends ApiController
{
    /**
     * @var array List of actions that require authentication.
     */
    protected array $authOnly = [
        'create',
        'result',
    ];

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['create', 'result'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['create', 'result'],
                        'roles' => ['roleUser'],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Returns the list of available payment drivers and the default one.
     *
     * @OA\Get(
     *     path="/api/payments",
     *     summary="Get list of available payment drivers and default one",
     *     tags={"Payment"},
     *     @OA\Response(
     *         response=200,
     *         description="List of available drivers and default driver",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="drivers",
     *                 type="array",
     *                 @OA\Items(type="string", example="paypal"),
     *                 @OA\Items(type="string", example="liqpay")
     *             ),
     *             @OA\Property(
     *                 property="default",
     *                 type="string",
     *                 example="paypal"
     *             )
     *         )
     *     )
     * )
     *
     * @return array
     */
    public function actionDrivers(): array
    {
        $drivers = Yii::$app->params['payment.drivers'] ?? [];
        $default = Yii::$app->params['payment.default'] ?? null;

        return [
            'drivers' => array_keys($drivers),
            'default' => $default,
        ];
    }

    /**
     * Creates a payment for the given order and driver.
     *
     * @OA\Post(
     *     path="/api/payments/create",
     *     security={{"bearerAuth":{}}},
     *     summary="API Create New Payment",
     *     description="Returns information about New Payment.",
     *     tags={"Payment"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount", "driver", "orderId"},
     *             @OA\Property(property="amount", type="string", example="100.00"),
     *             @OA\Property(property="driver", type="string", example="stripe"),
     *             @OA\Property(property="orderId", type="string", example="ORD-12345")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="payment", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     )
     * )
     *
     * @return array<string,mixed>
     * @throws BadRequestHttpException
     */
    public function actionCreate(): array
    {
        $body = Yii::$app->request->bodyParams;
        Yii::info("(!!!) Create payment request body: " . var_export($body, true), __METHOD__);

        $amount = $body['amount'] ?? null;
        if (empty($amount)) {
            throw new BadRequestHttpException("Amount is required.");
        }

        $driverName = $body['pay_system'] ?? null;
        if (empty($driverName)) {
            throw new BadRequestHttpException("Driver Name is required.");
        }

        if (empty($body['order_id'])) {
            $orderId = 'ORD-' . date('Ymd-His') . '-' . Yii::$app->security->generateRandomString(6);
            $order = new Order();
            $order->user_id = Yii::$app->user->id; // Assuming user is authenticated
            $order->order_id = $orderId;
        } else {
            $order = Order::findOne(['order_id' => $body['order_id']]);
            if (!$order) {
                throw new BadRequestHttpException("Order not found.");
            }
            if ($order->payment_status !== 'pending') {
                throw new BadRequestHttpException("Order is not in a valid state for payment.");
            }
            $orderId = $order->order_id;
        }
        $order->amount      = $amount;     // Update provided amount
        $order->currency    = $body['currency'] ?? 'USD';
        $order->pay_system  = $driverName; // Update pay_system
        $order->description = 'Payment for Order #' . $orderId;

        Yii::info("(!!!) Creating order: " . var_export($order->attributes, true), __METHOD__);

        if (!$order->save()) {
            throw new ServerErrorHttpException("Failed to create order: " . implode(', ', $order->getFirstErrors()));
        }

        try {
            // Creating a payment via PaymentManager
            $driver = Yii::$app->payment->getDriver($driverName);

            $paymentData = $driver->createPayment([
                'order_id'    => $orderId,
                'amount'      => $order->amount,
                'currency'    => $order->currency,
                'description' => $order->description,
            ]);

            return [
                'success' => true,
                'payment' => $paymentData,
                'orderId' => $orderId,
            ];
        } catch (\Throwable $e) {
            Yii::error("Failed to create payment: {$e->getMessage()}", __METHOD__);
            return [
                'success' => false,
                'message' => 'Payment creation failed. Please try again later.',
            ];
        }
    }

    /**
     * Handles the callback from payment providers.
     *
     * @OA\Post(
     *     path="/api/payments/handle/{driverName}",
     *     summary="Handle payment provider callback",
     *     description="Processes the callback sent by a payment provider (e.g., Stripe, PayPal, LiqPay).",
     *     tags={"Payment"},
     *     @OA\Parameter(
     *         name="driverName",
     *         in="path",
     *         description="Payment driver name",
     *         required=true,
     *         @OA\Schema(type="string", example="stripe")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Raw POST data, varies depending on payment driver",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 additionalProperties=true
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Callback processed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     )
     * )
     *
     * @param string $driverName
     * @return array<string,mixed>
     * @throws BadRequestHttpException
     * @throws ServerErrorHttpException
     */
    public function actionHandle(string $driverName): array
    {
        // Collect callback data depending on the driver
        if ($driverName === 'stripe') {
            $data = [
                'payload'   => Yii::$app->request->rawBody,
                'signature' => Yii::$app->request->headers->get('Stripe-Signature'),
            ];

            if (empty($data['payload']) || empty($data['signature'])) {
                throw new BadRequestHttpException("Invalid Stripe callback: missing payload or signature.");
            }
        } else {
            $data = Yii::$app->request->post();
            if (empty($data)) {
                throw new BadRequestHttpException("Empty callback data for driver: {$driverName}.");
            }
        }

        $driver = Yii::$app->payment->getDriver($driverName);
        $order = $driver->handleCallback($data);
        if (!$order) {
            throw new ServerErrorHttpException("Order not found or callback processing failed.");
        }
        $order->paid_at = $order->payment_status === 'success' ? date('Y-m-d H:i:s') : null;
        $order->save(false); // disable validation, can be replaced with a transaction

        Yii::info("(!!!) Payment callback received for order #{$order->order_id} with status: {$order->payment_status}", __METHOD__);

        return ['success' => true];
    }

    /**
     * @OA\Get(
     *     path="/api/payments/result",
     *     security={{"bearerAuth":{}}},
     *     summary="API Payments Result",
     *     description="Returns information about Payment Result.",
     *     tags={"Payment"},
     *     @OA\Parameter(
     *         name="orderId",
     *         in="query",
     *         required=true,
     *         description="Order ID to get payment result for",
     *         @OA\Schema(type="string", example="ORD-20250529-045325-abcd1234")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Processed payment result",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="order", type="object", example="{order_id: 123456, amount: 100.00, currency: USD}")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     )
     * )
     */
    public function actionResult($orderId): array
    {
        $order = Order::findOne(['order_id' => $orderId]);
        if (!$order) {
            throw new ServerErrorHttpException("Order not found.");
        }
        return [
            'success' => $order->payment_status === 'success',
            'order' => [
                'order_id' => $order->order_id,
                'amount'   => $order->amount,
                'currency' => $order->currency,
                'status'   => $order->payment_status,
                'paid_at'  => $order->paid_at,
            ],
        ];
    }
}
