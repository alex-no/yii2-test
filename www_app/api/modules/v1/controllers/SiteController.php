<?php
namespace app\api\modules\v1\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;

/**
 * @OA\Info(
 *     title="My Yii2 API",
 *     version="0.1",
 *     description="API documentation for the Yii2-test project"
 * )
 */
class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => \yii\filters\ContentNegotiator::class,
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }

    /**
     * @OA\Get(
     *     path="/api",
     *     summary="API Root Info",
     *     tags={"system"},
     *     @OA\Response(
     *         response="200",
     *         description="Information about version, language, and timezone",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="projectName", type="string"),
     *             @OA\Property(property="version", type="string"),
     *             @OA\Property(property="language", type="string"),
     *             @OA\Property(property="timeZone", type="string"),
     *         )
     *     )
     * )
     */
    public function actionIndex()
    {
        $app = app();
        return [
            'api' => 'Test API',
            'projectName' => $app->name,
            'version' => $app->version,
            'language' => $app->language,
            'timeZone' => $app->timeZone,
        ];
    }

    /**
     * @OA\Get(
     *     path="/api/db-tables",
     *     summary="List database tables",
     *     @OA\Response(
     *         response="200",
     *         description="Returns list of table names",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="tables",
     *                 type="array",
     *                 @OA\Items(type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function actionDbTables()
    {
        $tables = Yii::$app->db->schema->getTableNames();

        return [
            'tables' => $tables,
        ];
    }
}
