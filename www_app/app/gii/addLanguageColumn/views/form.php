<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var yii\widgets\ActiveForm $form */
/** @var app\gii\addLanguageColumn\AddLanguageColumnGenerator $generator */
?>

<?php if ($generator->hasErrors()): ?>
    <div class="alert alert-danger">
        <?= Html::errorSummary($generator) ?>
    </div>
<?php endif; ?>

<?= $form->field($generator, 'newLanguageSuffix')->textInput([
    'maxlength' => true,
    'placeholder' => 'e.g., fr'
]) ?>

<?= $form->field($generator, 'languages')->checkboxList(
    $generator->availableLanguages,
    [
        'separator' => '<br>',
    ]
) ?>

<?= $form->field($generator, 'position')->dropDownList(
    $generator->getPositionOptions(),
    [
        'options' => [
            'before_all' => ['class' => 'text-primary font-weight-bold'],
            'after_all' => ['class' => 'text-primary font-weight-bold'],
        ],
    ]
) ?>
