<?php
/** @var yii\web\View $this */
/** @var yii\widgets\ActiveForm $form */
/** @var app\generators\addLanguageColumn\AddLanguageColumnGenerator $generator */
?>

<?= $form->field($generator, 'newLanguageSuffix')->textInput(['maxlength' => 2]) ?>

<?= $form->field($generator, 'languages')->checkboxList($generator->getAvailableLanguages()) ?>

<?= $form->field($generator, 'position')->dropDownList($generator->getPositionOptions()) ?>
