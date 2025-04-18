<?php
use yii\helpers\ArrayHelper;
//use yii\helpers\StringHelper;
use yii\helpers\Html;

/**
 * Returns the application instance
 */
function app()
{
    return Yii::$app;
}

/**
 * Get a parameter from the configuration
 */
function param($key, $default = null)
{
    return Yii::$app->params[$key] ?? $default;
}

/**
 * Get a translation
 */
function t($category, $message, $params = [], $language = null)
{
    return Yii::t($category, $message, $params, $language);
}

/**
 * Current user
 */
function user()
{
    return Yii::$app->user->identity;
}

/**
 * Gets the current user's ID.
 *
 * @return int|null The user ID or null if the user is not authenticated.
 */
function user_id(): ?int
{
    return Yii::$app->user->id ?? null;
}

/**
 * Guest?
 */
function is_guest(): bool
{
    return Yii::$app->user->isGuest;
}

/**
 * Path to the web (public) directory
 */
function webroot($path = '')
{
    return Yii::getAlias('@webroot') . ($path ? DIRECTORY_SEPARATOR . $path : '');
}

/**
 * Path to the web URL
 */
function weburl($path = '')
{
    return Yii::getAlias('@web') . ($path ? '/' . ltrim($path, '/') : '');
}

/**
 * Get a value from an array by key
 */
function array_get($array, $key, $default = null)
{
    return ArrayHelper::getValue($array, $key, $default);
}

/**
 * Set a value in an array by key
 */
function array_set(&$array, $key, $value)
{
    return ArrayHelper::setValue($array, $key, $value);
}

/**
 * Random string
 */
function str_random($length = 16)
{
    return Yii::$app->security->generateRandomString($length);
}

/**
 * Escape HTML
 */
function e($string)
{
    return Html::encode($string);
}

/**
 * Output a JS variable
 */
function js_var($name, $value)
{
    return "<script>var {$name} = " . json_encode($value) . ";</script>";
}
