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

    protected function validateRules($rules): array
    {
        $results = [];

        foreach ($rules as $rule) {
            $route = $rule->route ?? null;
            $isValid = true;
            $error = null;
            $methods = $rule->verb ?? ['GET', 'POST'];

            if (is_string($route)) {
                // Example: v1/user/view
                $segments = explode('/', $route);
                if (count($segments) < 2) {
                    $isValid = false;
                    $error = 'Invalid route format';
                } else {
                    // Parsing: module/controller/action or controller/action
                    $action = array_pop($segments);
                    $controllerId = array_pop($segments);
                    $modulePath = implode('/', $segments);

                    $namespaceBase = 'app\\' . $this->appContext;
                    $namespace = $namespaceBase . '\\controllers';

                    $controllerClass = $namespace . '\\' . $this->idToCamel($controllerId) . 'Controller';

                    // Consider nested modules, if any
                    if ($modulePath) {
                        $namespace = $namespaceBase . '\\modules\\' . str_replace('/', '\\modules\\', $modulePath) . '\\controllers';
                        $controllerClass = $namespace . '\\' . $this->idToCamel($controllerId) . 'Controller';
                    }

                    if (!class_exists($controllerClass)) {
                        $isValid = false;
                        $error = "Class {$controllerClass} not found";
                    } else {
                        // Check if the action exists
                        try {
                            $controller = Yii::createObject($controllerClass, ['id' => $controllerId, 'module' => Yii::$app->controller->module]);
                            $actionMethod = 'action' . ucfirst($action);
                            if (!method_exists($controller, $actionMethod)) {
                                $isValid = false;
                                $error = "Method {$actionMethod}() not found in {$controllerClass}";
                            }
                        } catch (\Throwable $e) {
                            $isValid = false;
                            $error = "Error creating controller: " . $e->getMessage();
                        }
                    }
                }
            }

            $results[] = [
                'methods' => implode(', ', $methods), // Methods (GET, POST etc.)
                'pattern' => $rule->name ?? '',
                'route' => $route,
                'class' => get_class($rule),
                'valid' => $isValid,
                'error' => $error,
            ];
        }

        return $results;
    }

    protected function idToCamel($id)
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $id)));
    }
}
