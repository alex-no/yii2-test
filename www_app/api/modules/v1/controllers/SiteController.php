<?php
namespace app\api\modules\v1\controllers;

// use Yii;
use app\api\components\ApiController;

/**
 * @OA\Info(
 *     title="Yii2 API-test",
 *     version="0.1",
 *     description="API documentation for the Yii2-test project"
 * )
 */
class SiteController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api",
     *     summary="API Root Info",
     *     tags={"About system"},
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
            'api' => 'Test Yii2-API',
            'projectName' => $app->name,
            'version' => $app->version,
            'language' => $app->language,
            'timeZone' => $app->timeZone,
        ];
    }
}
