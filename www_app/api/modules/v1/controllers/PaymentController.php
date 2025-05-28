<?php
namespace app\api\modules\v1\controllers;

use Yii;
use app\api\components\ApiController;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;
use app\models\Order;

/**
 * PaymentController class for working with Payment models.
 *
 * @OA\Tag(
 *     name="Payment",
 *     description="API for working with payment models, including creating payments, handling callbacks, and verifying signatures.",
 * )
 */
class PaymentController extends ApiController
{
    /**
     * @OA\Post(
     *     path="/api/payments",
     *     summary="API Payments",
     *     description="Returns information about the API, including project name, version, language, and timezone.",
     *     tags={"Payment"},
     *     @OA\Response(
     *         response="200",
     *         description="Information about version, language, and timezone",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="sum", type="string"),
     *         )
     *     )
     * )
     */
    public function actionCreate()
    {
        $request = Yii::$app->request->post();

        $order_id = $request['order_id'] ?? 'ORD-' . date('Ymd-His') . '-' . Yii::$app->security->generateRandomString(6);

        // Creating a payment via PaymentManager
        $paymentData = Yii::$app->payment->getDriver()->createPayment([
            'order_id'    => $order_id,
            'amount'      => $request['amount'],
            'currency'    => $order->currency ?? 'UAH',
            'description' => 'Payment for Order #' . $order_id,
            'result_url'  => Yii::$app->request->hostInfo . '/success',
        ]);

        return [
            'success' => true,
            'payment' => $paymentData,
        ];
    }

    public function actionHandle()
    {
        $request = Yii::$app->request->post();

        if (empty($request['data']) || empty($request['signature'])) {
            throw new BadRequestHttpException("Missing data or signature.");
        }

        $driver = Yii::$app->payment->getDriver();

        if (!$driver->verifySignature($request['data'], $request['signature'])) {
            throw new BadRequestHttpException("Invalid signature.");
        }

        $data = $driver->handleCallback($request);

        $orderId = $data['order_id'] ?? null;
        $status = $data['status'] ?? null;

        if (!$orderId || !$status) {
            throw new BadRequestHttpException("Invalid callback data.");
        }

        $order = Order::findOne(['id' => $orderId]);
        if (!$order) {
            throw new ServerErrorHttpException("Order not found.");
        }

        $order->payment_status = $status;
        $order->save(false); // disable validation, can be replaced with a transaction

        Yii::info("Payment callback received for order #$orderId with status: $status", __METHOD__);

        return ['success' => true];
    }

    public function actionResult()
    {
    }
}
