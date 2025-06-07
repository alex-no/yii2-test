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
     * @OA\Post(
     *     path="/api/payments/create",
     *     security={{"bearerAuth":{}}},
     *     summary="API Create New Payment",
     *     description="Returns information about New Payment.",
     *     tags={"Payment"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"amount", "pay_system"},
     *             @OA\Property(property="amount", type="string", example="100.00"),
     *             @OA\Property(property="pay_system", type="string", example="lyqpay"),
     *             @OA\Property(property="order_id", type="string", example="ORD-20250529-045325-abcd1234")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Created new payment",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="payment", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     )
     * )
     */
    public function actionCreate(): array
    {
        $post = Yii::$app->request->post();

        if (empty($post['amount'])) {
            throw new BadRequestHttpException("Amount is required.");
        }

        $driverName = $post['pay_system'] ?? null;
        if (empty($driverName)) {
            throw new BadRequestHttpException("Driver Name is required.");
        }

        if (empty($post['order_id'])) {
            $orderId = 'ORD-' . date('Ymd-His') . '-' . Yii::$app->security->generateRandomString(6);
            $order = new Order();
            $order->user_id = Yii::$app->user->id; // Assuming user is authenticated
            $order->order_id = $orderId;
            $order->currency = 'UAH';
            $order->description = 'Payment for Order #' . $orderId;
        } else {
            $order = Order::findOne(['order_id' => $post['order_id']]);
            if (!$order) {
                throw new BadRequestHttpException("Order not found.");
            }
            if ($order->payment_status !== 'pending') {
                throw new BadRequestHttpException("Order is not in a valid state for payment.");
            }
            $orderId = $order->order_id;
        }
        $order->amount = $post['amount'];   // Update provided amount
        $order->pay_system = $driverName;   // Update pay_system

        if (!$order->save()) {
            throw new ServerErrorHttpException("Failed to create order: " . implode(', ', $order->getFirstErrors()));
        }

        // Creating a payment via PaymentManager
        $paymentData = Yii::$app->payment->getDriver($driverName)->createPayment([
            'order_id'    => $orderId,
            'amount'      => $post['amount'],
            'currency'    => $order->currency,
            'description' => 'Payment for Order #' . $orderId,
            'result_url'  => Yii::$app->request->hostInfo . '/api/payments/success',
        ]);

        return [
            'success' => true,
            'payment' => $paymentData,
            'orderId' => $orderId,
        ];
    }

    /**
     * @OA\Post(
     *     path="/api/payments/handle",
     *     summary="API Payments Handle",
     *     description="Returns information about Handle Payments.",
     *     tags={"Payment"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object", example="{data: 'example_data'}"),
     *             @OA\Property(property="signature", type="string", example="c2lnbmF0dXJlX2V4YW1wbGU=")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Updated",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     )
     * )
     */
    public function actionHandle(): array
    {
        $post = Yii::$app->request->post();

        if (empty($post['data']) || empty($post['signature'])) {
            throw new BadRequestHttpException("Missing data or signature.");
        }

        $driver = Yii::$app->payment->getDriver();

        if (!$driver->verifySignature($post['data'], $post['signature'])) {
            throw new BadRequestHttpException("Invalid signature.");
        }

        $data = $driver->handleCallback($post);

        $orderId = $data['order_id'] ?? null;
        $status = $data['status'] ?? null;

        if (!$orderId || !$status) {
            throw new BadRequestHttpException("Invalid callback data.");
        }

        $order = Order::findOne(['order_id' => $orderId]);
        if (!$order) {
            throw new ServerErrorHttpException("Order not found.");
        }

        $order->payment_status = $status;
        $order->paid_at = $status === 'success' ? date('Y-m-d H:i:s') : null;
        $order->save(false); // disable validation, can be replaced with a transaction

        Yii::info("Payment callback received for order #$orderId with status: $status", __METHOD__);

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
