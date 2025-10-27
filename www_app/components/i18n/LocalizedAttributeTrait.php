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

    /**
     * Initialize localized settings from global configuration.
     * @return void
     * @throws MissingLocalizedAttributeException
     */
    public function init(): void
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

    /**
     * Get the localized attribute name based on the current application language.
     * @param string $name The base attribute name.
     * @return string The localized attribute name.
     * @throws MissingLocalizedAttributeException If the localized attribute does not exist and isStrict is true.
     */
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
     * @return array
     * @throws MissingLocalizedAttributeException
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
