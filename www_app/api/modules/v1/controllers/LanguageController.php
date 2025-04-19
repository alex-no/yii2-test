<?php
namespace app\api\modules\v1\controllers;

use yii\rest\Controller;
use app\models\Language;
use yii\web\Response;
use Yii;

class LanguageController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        // Ensure the response is in JSON format
        $behaviors['contentNegotiator']['formats']['application/json'] = Response::FORMAT_JSON;
        return $behaviors;
    }

    /**
     * @OA\Get(
     *     path="/languages",
     *     summary="Get list of languages",
     *     description="Returns a list of all available languages",
     *     tags={"Languages"},
     *     @OA\Response(
     *         response=200,
     *         description="A list of languages",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="en"),
     *                 @OA\Property(property="short_name", type="string", example="Eng"),
     *                 @OA\Property(property="full_name", type="string", example="English"),
     *                 @OA\Property(property="is_enabled", type="integer", example=1),
     *                 @OA\Property(property="order", type="integer", example=2)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function actionIndex()
    {
        return Language::find()->all();
    }
}
