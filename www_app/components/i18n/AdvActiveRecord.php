<?php
namespace app\components\i18n;

use yii\db\ActiveRecord;

class AdvActiveRecord extends ActiveRecord
{
    use LocalizedAttributeTrait;

    public function __get($name)
    {
        $localized = $this->getLocalizedAttributeName($name);
        return parent::__get($localized);
    }

    public function __set($name, $value)
    {
        $localized = $this->getLocalizedAttributeName($name);

        return parent::__set($localized, $value);
    }
}
