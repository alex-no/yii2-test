<?php

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\model\Generator */
/* @var $tableName string */
/* @var $className string */
/* @var $queryClassName string */
/* @var $tableSchema yii\db\TableSchema */
/* @var $properties array */
/* @var $labels string[] */
/* @var $rules string[] */
/* @var $relations array */

echo "<?php\n";
?>

namespace <?= $generator->ns ?>\base;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the base model class for table "<?= $tableName ?>".
 *
<?php foreach ($properties as $property): ?>
 * @property <?= $property['type'] . ' $' . $property['name'] . "\n" ?>
<?php endforeach; ?>
 */
class <?= $className ?>Base extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '<?= $tableName ?>';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
<?= empty($rules) ? '' : '            ' . implode(",\n            ", $rules) . ",\n" ?>
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
<?= empty($labels) ? '' : '            ' . implode(",\n            ", array_map(
    fn($name, $label) => "'$name' => " . var_export($label, true),
    array_keys($labels),
    $labels
)) . ",\n" ?>
        ];
    }

<?php if (!empty($relations)): ?>

    // Relations
<?= implode("\n\n", $relations) ?>

<?php endif; ?>
}
