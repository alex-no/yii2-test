<?php

namespace app\models;

use Yii;
use app\models\base\PetOwner as PetOwnerBase;
use app\models\PetBreed;

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

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        $dirty = $this->getDirtyAttributes();

        if (empty($this->user_id)) {
            $this->user_id = Yii::$app->user->id;
        }

        if (isset($dirty['pet_breed_id'])) {
            $petBreed = PetBreed::find()
                ->where(['id' => $this->pet_breed_id])
                ->one();
            if ($petBreed) {
                $this->pet_type_id = $petBreed->pet_type_id;
            }
        }

        return parent::beforeValidate();
    }
}
