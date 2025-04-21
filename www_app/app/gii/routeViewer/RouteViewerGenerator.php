<?php
namespace app\gii\routeViewer;

use Yii;
use yii\gii\Generator;
use yii\web\Application;
use yii\web\UrlManager;

class RouteViewerGenerator extends Generator
{
    public $appContext = 'api'; // Default context
    public $filter = ''; // Filter for route patterns

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
            [['appContext', 'filter'], 'safe'], // "safe" — for mass assignment
            [['appContext'], 'in', 'range' => ['web', 'api']],
        ];
    }

    public function getRoutes()
    {
        $urlManager = $this->appContext === 'api'
        ? $this->loadUrlManagerFromApi()
        : Yii::$app->urlManager;

        $rules = $urlManager->rules;


        if ($this->filter) {
            $rules = array_filter($rules, function ($rule) {
                $pattern = is_object($rule) && property_exists($rule, 'name') ? $rule->name : $rule->route;
                return stripos($pattern, $this->filter) === 0;
            });
        }

        return $this->validateRules($rules);
    }

    protected function loadUrlManagerFromApi(): UrlManager
    {
        // Manually include the API configuration
        $config = require(Yii::getAlias('@app/config/api.php'));

        // Set the URL manager to the API context
        $urlManagerConfig = $config['components']['urlManager'] ?? [];
        $urlManager = Yii::createObject(array_merge(
            ['class' => UrlManager::class],
            $urlManagerConfig
        ));
        return $urlManager;
    }

    protected function validateRules($rules)
    {
        $results = [];

        foreach ($rules as $rule) {
            $route = $rule->route ?? null;

            $isValid = true;

            // Attempting to create a controller
            if (is_string($route)) {
                try {
                    [$controller, $action] = Yii::$app->createController($route);
                    if (!$controller) {
                        $isValid = false;
                    }
                } catch (\Throwable $e) {
                    $isValid = false;
                }
            }

            $results[] = [
                'pattern' => $rule->name ?? '',
                'route' => $route,
                'class' => get_class($rule),
                'valid' => $isValid,
            ];
        }

        return $results;
    }
}
