<?php

namespace app\models;

use app\models\base\PetOwner as PetOwnerBase;

/**
 * Class PetOwner — extend your logic here.
 */
class PetOwner extends PetOwnerBase
{
    public function fields()
    {
        $fields = parent::fields();

        unset($fields['created_at']);

        $fields['user_name'] = function () {
            return $this->user ? $this->user->username : null;
        };
        $fields['pet_type_name'] = function () {
            return $this->petType ? $this->petType->{'@@name'} : null;
        };
        $fields['pet_breed_name'] = function () {
            return $this->petBreed ? $this->petBreed->{'@@name'} : null;
        };

        return $fields;
    }
}
