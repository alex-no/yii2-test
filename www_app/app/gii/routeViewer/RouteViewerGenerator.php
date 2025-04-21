<?php
namespace app\gii\routeViewer;

use Yii;
use yii\gii\Generator;
use yii\web\Application;
use yii\web\UrlManager;

class RouteViewerGenerator extends Generator
{
    public $appContext = 'api'; // Default context

    public function getName()
    {
        return 'Route Viewer';
    }

    public function getDescription()
    {
        return 'Displays a list of all registered URL routes.';
    }

    public function generate()
    {
        return [];
    }

    public function formView()
    {
        return '@gii/routeViewer/views/form.php';
    }

    public function rules()
    {
        return [
            [['appContext'], 'in', 'range' => ['web', 'api']],
        ];
    }

    public function getRoutes()
    {
        if ($this->appContext === 'api') {
            // Manually include the API configuration
            $config = require(Yii::getAlias('@app/config/api.php'));

            // Set the URL manager to the API context
            $urlManagerConfig = $config['components']['urlManager'] ?? [];
            $urlManager = Yii::createObject(array_merge(
                ['class' => UrlManager::class],
                $urlManagerConfig
            ));
            return $urlManager->rules;
        }

        // By default — the current application
        return Yii::$app->urlManager->rules;
    }

}
