<?php

namespace app\components\i18n;

use yii\data\ActiveDataProvider;

/**
 * Extended ActiveDataProvider with support for localized attributes.
 */
class AdvActiveDataProvider extends ActiveDataProvider
{
    use LocalizedAttributeTrait;

    public function init()
    {
        parent::init();

        if ($this->query && method_exists($this->query->modelClass, 'getLocalizedSettings')) {
            /** @var LocalizedAttributeTrait $model */
            $model = new $this->query->modelClass();
            $settings = $model->getLocalizedSettings();

            foreach ($settings as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }

        $this->processSortAttributes();
    }

    protected function processSortAttributes(): void
    {
        if (!$this->sort || !$this->sort->attributes) {
            return;
        }

        $localizedAttributes = [];
        foreach ($this->sort->attributes as $name => $definition) {
            $localizedName = $this->getLocalizedAttributeName($name);
            $localizedAttributes[$localizedName] = $definition;
        }
        $this->sort->attributes = $localizedAttributes;

        if (!empty($this->sort->defaultOrder)) {
            $localizedOrder = [];
            foreach ($this->sort->defaultOrder as $attribute => $direction) {
                $localizedName = $this->getLocalizedAttributeName($attribute);
                $localizedOrder[$localizedName] = $direction;
            }
            $this->sort->defaultOrder = $localizedOrder;
        }
    }
}
