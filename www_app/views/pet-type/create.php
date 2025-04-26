<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\PetType $model */

$this->title = Yii::t('app', 'Create Pet Type');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Pet Types'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pet-type-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
