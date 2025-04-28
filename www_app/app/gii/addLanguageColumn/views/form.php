<?php
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\widgets\ActiveForm $form */
/** @var app\gii\addLanguageColumn\AddLanguageColumnGenerator $generator */
?>

<?= $form->field($generator, 'newLanguageSuffix')->textInput([
    'maxlength' => true,
    'placeholder' => 'e.g., fr'
]) ?>

<div class="form-group">
    <?= Html::label('Available Languages', 'languages', ['class' => 'control-label']) ?>
    <div>
        <?php foreach ($generator->availableLanguages as $code => $label): ?>
            <div class="checkbox">
                <label>
                    <?= Html::checkbox('Generator[languages][]', in_array($code, (array) $generator->languages), [
                        'value' => $code,
                    ]) ?>
                    <?= Html::encode($label) ?>
                </label>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="form-group field-generator-position">
    <?= Html::label('Position to Insert', 'generator-position', ['class' => 'control-label']) ?>
    <select id="generator-position" class="form-control" name="Generator[position]">
        <?php foreach ($generator->getPositionOptions() as $value => $label): ?>
            <?php
            $isSpecial = in_array($value, ['before_all', 'after_all']);
            $isSelected = ($generator->position === $value) || ($generator->position === null && $value === 'after_all');
            ?>
            <option value="<?= Html::encode($value) ?>"
                <?= $isSpecial ? 'class="text-primary font-weight-bold"' : '' ?>
                <?= $isSelected ? 'selected' : '' ?>
            >
                <?= Html::encode($label) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
