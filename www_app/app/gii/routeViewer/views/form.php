<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\gii\routeViewer\RouteViewerGenerator $generator */
$this->registerCss("#form-fields { max-width: 100% !important; flex: 0 0 100% !important; }");

$form = ActiveForm::begin();
echo $form->field($generator, 'appContext')->dropDownList([
    'web' => 'Web',
    'api' => 'API',
]);
echo $form->field($generator, 'filter')->textInput(['placeholder' => 'Filter by pattern prefix (e.g. user)']);
echo Html::tag('p', 'Note: The filter is case-insensitive and matches the beginning of the pattern.');
echo Html::submitButton('Refresh', ['class' => 'btn btn-primary']);
ActiveForm::end();

echo '<hr>';

echo $this->render('preview', [
    'routes' => $generator->getRoutes(),
]);
