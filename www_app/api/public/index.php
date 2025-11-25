<?php
//phpinfo();die();
/**
 * Yii web bootstrap file.
 *
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */
require __DIR__ . '/../../vendor/autoload.php';

// Load Dotenv
// die('Deprecated: Please use config/bootstrap.php to load environment variables.' . PHP_EOL);
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->safeLoad();

// Set debug and environment flags from .env
defined('YII_DEBUG') or define('YII_DEBUG', filter_var($_ENV['YII_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));
defined('YII_ENV') or define('YII_ENV', $_ENV['YII_ENV'] ?? 'prod');

require __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';

$config = require(__DIR__ . '/../../config/api.php');
(new app\base\Application($config))->run();
