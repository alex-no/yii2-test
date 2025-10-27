<?php
namespace app\components\i18n;

use yii\db\ActiveRecord;
/**
 * Class AdvActiveRecord
 * @package app\components\i18n
 * Extends Yii's ActiveRecord to support localized attributes.
 * Localized attributes are accessed by appending '@@' to the attribute name.
 * For example, to access the localized 'name_en' attribute, use 'name@@'.
 * @see LocalizedAttributeTrait
 * @see ActiveRecord
 * @author [Oleksandr Nosov]
 * @copyright [2025] [Oleksandr Nosov]
 * @license [License Type, GPL]
 * @version [Version Number, 1.0.0]
 */
class AdvActiveRecord extends ActiveRecord
{
    use LocalizedAttributeTrait;

    /**
     * {@inheritdoc}
     */
    public function getAttribute($name): mixed
    {
        $localized = $this->getLocalizedAttributeName($name);
        return parent::getAttribute($localized);
    }

    /**
     * {@inheritdoc}
     */
    protected function extractFieldsFor(array $fields, $rootField): array
    {
        return parent::extractFieldsFor($this->convertLocalizedFields($fields), $rootField);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(array $fields = [], array $expand = [], $recursive = true): array
    {
        return parent::toArray($this->convertLocalizedFields($fields), $expand, $recursive);
    }

    /**
     * Convert an array of field names to their localized equivalents.
     *
     * @param string[] $fields The array of field names to convert.
     * @return string[] The array of localized field names.
     * @throws MissingLocalizedAttributeException
     */
    protected function convertLocalizedFields(array $fields): array
    {
        return array_map([$this, 'getLocalizedAttributeName'], $fields);
        // return array_map(fn(string $f) => $this->getLocalizedAttributeName($f), $fields);
    }

    /**
     * {@inheritdoc}
     */
    public function __get($name): mixed
    {
        $getter = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }

        $localized = $this->getLocalizedAttributeName($name);
        return parent::__get($localized);
    }

    /**
     * {@inheritdoc}
     */
    public function __set($name, $value): void
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        }

        $localized = $this->getLocalizedAttributeName($name);
        parent::__set($localized, $value);
    }
}
