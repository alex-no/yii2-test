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

    public $configData = [];

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
        $contexts = $this->getConfig('contexts', [
            'web' => 'Web',
            'api' => 'API',
        ]);
        return [
            [['appContext', 'filter'], 'safe'], // "safe" — for mass assignment
            [['appContext'], 'in', 'range' => array_keys($contexts)],
        ];
    }

    public function getRoutes()
    {
        $urlManager = $this->appContext === $this->getConfig('currentUrlManager', 'web')
        ? Yii::$app->urlManager
        : $this->loadUrlManagerFromApi();

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
                if (preg_match('/<\w+/', $route)) {
                    // dynamic pattern like <controller>/<action>
                    $isValid = null; // neutral
                    $error = 'Dynamic route pattern – cannot resolve';
                } else {
                    $segments = explode('/', $route);
                    if (count($segments) < 2) {
                        $isValid = false;
                        $error = 'Invalid route format';
                    } else {
                        $action = array_pop($segments);
                        $controllerId = array_pop($segments);
                        $modulePath = implode('/', $segments);

                        $namespaceBase = 'app\\' . $this->appContext;
                        $namespace = $namespaceBase . '\\controllers';
                        $controllerClass = $namespace . '\\' . $this->idToCamel($controllerId) . 'Controller';

                        if ($modulePath) {
                            try {
                                $module = Yii::$app->getModule($modulePath);
                                if ($module && isset($module->controllerNamespace)) {
                                    $controllerClass = $module->controllerNamespace . '\\' . $this->idToCamel($controllerId) . 'Controller';
                                } else {
                                    $namespace = $namespaceBase . '\\modules\\' . str_replace('/', '\\modules\\', $modulePath) . '\\controllers';
                                    $controllerClass = $namespace . '\\' . $this->idToCamel($controllerId) . 'Controller';
                                }
                            } catch (\Throwable $e) {
                                $isValid = false;
                                $error = "Module `$modulePath` not found";
                            }
                        }

                        if ($isValid !== false && !class_exists($controllerClass)) {
                            $isValid = false;
                            $error = "Class {$controllerClass} not found";
                        } elseif ($isValid !== false) {
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
            }

            $status = $this->formatStatus($isValid, $error);

            $results[] = [
                'methods' => implode(', ', $methods), // Methods (GET, POST etc.)
                'pattern' => $rule->name ?? '',
                'route' => $route,
                'class' => get_class($rule),
                'valid' => $isValid,
                'error' => $error,
                'status_short' => $status['short'],
                'status_hint' => $status['hint'],
            ];
        }

        return $results;
    }

    protected function idToCamel($id)
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $id)));
    }

    protected function formatStatus(?bool $isValid, ?string $error): array
    {
        if ($isValid === true) {
            return ['short' => '✅ OK', 'hint' => ''];
        }

        if ($error === null) {
            return ['short' => '⚠️ unknown', 'hint' => 'Unknown issue'];
        }

        $short = '❌';
        $errorLower = strtolower($error);

        if (str_contains($errorLower, 'dynamic route')) {
            $short = '⚠️ cannot resolve';
        } elseif (str_contains($errorLower, 'not found')) {
            $short = '❌ not found';
        } elseif (str_contains($errorLower, 'invalid route')) {
            $short = '❌ invalid format';
        }

        return ['short' => $short, 'hint' => $error];
    }

    public function getConfig($key, $default = [])
    {
        return $this->configData[$key] ?? $default;
    }
}
