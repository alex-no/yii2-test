<?php

namespace app\components\i18n;

use Yii;
use app\components\i18n\MissingLocalizedAttributeException;

trait LocalizedAttributeTrait
{
    /**
     * Prefix for localized attributes, configurable.
     * Example: "@@" results in `@@title` → `title_en`
     */
    public string $localizedPrefixes = '@@';

    public function init()
    {
        if (is_callable('parent::init')) {
            parent::init();
        }

        // Key by class name
        $class = static::class;
        $class2 = self::class;
        $globalConfig = Yii::$app->params['advActive'] ?? [];
        $config = $globalConfig[$class] ?? ($globalConfig[$class2] ?? []);

        foreach ($config as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
                // Only if it has not been overridden manually
                // if (!isset($this->$key) || $this->$key === (new static)->$key) {
                //     $this->$key = $value;
                // }
            }
        }
    }

    protected function getLocalizedAttributeName(string $name): string
    {
        if (!str_starts_with($name, $this->localizedPrefixes)) {
            return $name;
        }

        $lang = Yii::$app->language;
        $baseName = substr($name, strlen($this->localizedPrefixes));

        $localized = "{$baseName}_{$lang}";

        if (!$this->hasAttribute($localized)) {
            throw new MissingLocalizedAttributeException($localized);
        }

        return $localized;
    }
}
