<?php

namespace app\components\i18n;

use Yii;

trait LocalizedAttributeTrait
{
    public string $localizedPrefixes = '@@';

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
