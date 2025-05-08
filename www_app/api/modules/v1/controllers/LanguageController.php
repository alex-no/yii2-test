<?php
namespace app\api\modules\v1\controllers;

// use Yii;
use app\api\components\ApiController;
use app\models\Language;

class LanguageController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/languages",
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

    /**
     * @OA\Get(
     *     path="/languages/{code}",
     *     summary="Get language by Code",
     *     description="Returns a single language by its Code",
     *     operationId="getLanguageById",
     *     tags={"Languages"},
     *     @OA\Parameter(
     *         name="code",
     *         in="query",
     *         required=true,
     *         description="Code of the language to retrieve",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="code",
     *                 type="string",
     *                 example="en",
     *                 description="Language code"
     *             ),
     *             @OA\Property(
     *                 property="short_name",
     *                 type="string",
     *                 example="Eng",
     *                 description="Short name of the language"
     *             ),
     *             @OA\Property(
     *                 property="full_name",
     *                 type="string",
     *                 example="English",
     *                 description="Full name of the language"
     *             ),
     *             @OA\Property(
     *                 property="is_enabled",
     *                 type="integer",
     *                 example=1,
     *                 description="Indicates if the language is enabled (1) or disabled (0)"
     *             ),
     *             @OA\Property(
     *                 property="order",
     *                 type="integer",
     *                 example=2,
     *                 description="Order of the language in the list"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function actionView($code)
    {
        return Language::findOne($code);
    }
}
