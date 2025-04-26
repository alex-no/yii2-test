<?php

namespace app\components\i18n;

use Yii;
use app\components\i18n\MissingLocalizedAttributeException;

/**
 * Trait for working with localized attributes.
 */
trait LocalizedAttributeTrait
{
    /**
     * Prefix for localized attributes, configurable.
     * Example: "@@" results in `@@title` → `title_en`
     */
    public string $localizedPrefixes = '@@';

    public bool $isStrict = true;

    public string $defaultLanguage = 'en';

    public function init()
    {
        if (is_callable('parent::init')) {
            parent::init();
        }

        // Key by class name
        $class = isset($this->modelClass) ? $this->modelClass : static::class; // Model class name
        $class2 = self::class; // Base class name AdvActiveRecord or AdvActiveQuery
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

        if (method_exists($this, 'hasAttribute') && !$this->hasAttribute($localized)) {
            if ($this->isStrict) {
                throw new MissingLocalizedAttributeException($localized);
            }
            $localized = "{$baseName}_{$this->defaultLanguage}";
        }

        return $localized;
    }

    /**
     * Returns localized settings for external usage (e.g., in DataProvider).
     */
    public function getLocalizedSettings(): array
    {
        return [
            'localizedPrefixes' => $this->localizedPrefixes,
            'defaultLanguage' => $this->defaultLanguage,
            'isStrict' => $this->isStrict,
        ];
    }
}
