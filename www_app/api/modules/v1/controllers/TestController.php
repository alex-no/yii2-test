<?php
namespace app\api\modules\v1\controllers;

use Yii;
use app\api\components\ApiController;
use app\common\services\EmailService;

class TestController extends ApiController
{
    protected array $authOnly = ['db-tables'];

    /**
     * @OA\Get(
     *     path="/api/test",
     *     summary="Test index",
     *     tags={"Test"},
     *     description="Returns a simple test boolean flag",
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="test",
     *                 type="boolean",
     *                 example=true
     *             )
     *         )
     *     )
     * )
     */
    public function actionIndex()
    {

        return [
            'test' => true,
        ];
    }

    /**
     * @OA\Get(
     *     path="/api/server-clock",
     *     summary="Get current server time",
     *     tags={"Test"},
     *     description="Returns the current server time and timezone",
     *     @OA\Response(
     *         response=200,
     *         description="Current server time information",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="clientName",
     *                 type="string",
     *                 example="IpGeoLocation"
     *             ),
     *             @OA\Property(
     *                 property="now",
     *                 type="string",
     *                 format="date-time",
     *                 example="2025-05-08 12:51:35"
     *             ),
     *             @OA\Property(
     *                 property="timezone",
     *                 type="string",
     *                 example="Europe/Kyiv"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Clock service error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unable to reach clock service")
     *         )
     *     )
     * )
     */
    public function actionServerClock()
    {
        $clock = Yii::$app->serverClock;
        return [
            'clientName' => $clock->getClientName(),
            'now' => $clock->now()->format('Y-m-d H:i:s'),
            'timezone' => $clock->getTimezone()->getName(),
        ];
    }

    /**
     * @OA\Get(
     *     path="/api/db-tables",
     *     summary="List database tables",
     *     tags={"Test"},
     *     security={{"bearerAuth":{}}},
     *     description="Returns the names of all tables in the connected database",
     *     @OA\Response(
     *         response=200,
     *         description="List of table names",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Database connection is successful"
     *             ),
     *             @OA\Property(
     *                 property="tables",
     *                 type="array",
     *                 @OA\Items(type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Database connection error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Could not connect to the database"),
     *             @OA\Property(property="error", type="string", example="SQLSTATE[HY000] [1045] Access denied for user...")
     *         )
     *     )
     * )
     */
    public function actionDbTables()
    {
        if (Yii::$app->user->isGuest) {
            Yii::$app->response->statusCode = 401;
            return ['message' => 'Unauthorized'];
        }

        try {
            $tables = Yii::$app->db->schema->getTableNames();

            return [
                'message' => 'Database connection is successful',
                'tables' => $tables,
            ];
        } catch (\Exception $e) {
            return [
                'message' => 'Could not connect to the database. Please check your configuration.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * @OA\Get(
     *     path="/api/mail-test",
     *     summary="Mail Test",
     *     tags={"Test"},
     *     description="Check if the mail service is working by sending a test email",
     *     @OA\Response(
     *         response=200,
     *         description="Email sent successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Email sent successfully"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to send email",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to send email"),
     *             @OA\Property(property="success", type="boolean", example=false)
     *         )
     *     )
     * )
     */
    public function actionMailTest(): array
    {
        $service = new EmailService();
        $sent = $service->sendConfirmation('alex@4n.com.ua', 'Alex', 'http://yii.loc/');

        if ($sent) {
            return ['success' => true, 'message' => 'Email sent successfully'];
        } else {
            Yii::$app->response->statusCode = 500;
            return ['success' => false, 'message' => 'Failed to send email'];
        }
    }

    public function actionLangDebug(): string
    {
        // Если вы регистрировали адаптер как компонент (languageSelector)
        if (Yii::$app->has('languageSelector')) {
            $lang = Yii::$app->languageSelector->detect(false);
            return "languageSelector detect(): $lang (Yii::app->language = " . Yii::$app->language . ")";
        }

        // или если ваш bootstrap создаёт ядро напрямую, можно прочитать Yii::$app->language
        return "Yii::\$app->language = " . Yii::$app->language;
    }


}
