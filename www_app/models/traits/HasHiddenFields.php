<?php

namespace app\models\traits;

use Yii;

trait HasHiddenFields
{
    public array $hiddenFields = [];

    public function toPublicArray(): array
    {
        $attributes = $this->getAttributes();

        foreach ($this->getHiddenFields() as $field) {
            unset($attributes[$field]);
        }

        return $attributes;
    }

    public function getHiddenFields(): array
    {
        $default = param('hiddenFields', []);
        return array_unique(array_merge($default[static::class] ?? [], $this->hiddenFields));
    }
}
