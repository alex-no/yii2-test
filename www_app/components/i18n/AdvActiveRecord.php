<?php
namespace app\components\i18n;

use yii\db\ActiveRecord;

class AdvActiveRecord extends ActiveRecord
{
    use LocalizedAttributeTrait;

    public function __get($name)
    {
        $getter = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }

        $localized = $this->getLocalizedAttributeName($name);
        return parent::__get($localized);
    }

    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            return $this->$setter($value);
        }

        $localized = $this->getLocalizedAttributeName($name);
        return parent::__set($localized, $value);
    }
}
