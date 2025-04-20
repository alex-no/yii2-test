<?php

namespace app\models\traits;

interface HasHiddenFieldsInterface
{
    /**
     * Returns the public array of the model (excluding hidden fields)
     *
     * @return array
     */
    public function toPublicArray(): array;

    /**
     * Returns an array of hidden fields
     *
     * @return string[]
     */
    public function getHiddenFields(): array;
}
