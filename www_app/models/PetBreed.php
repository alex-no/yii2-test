<?php

namespace app\models;

use app\models\base\PetBreed as PetBreedBase;

/**
 * Class PetBreed — extend your logic here.
 */
class PetBreed extends PetBreedBase
{
    public function fields()
    {
        $fields = parent::fields();

        unset($fields['created_at']);

        $fields['pet_type_name'] = function () {
            return $this->petType ? $this->petType->{'@@name'} : null;
        };

        return $fields;
    }
}
