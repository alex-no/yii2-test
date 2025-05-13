<?php
namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\rbac\DbManager;

class RbacController extends Controller
{
    /** @var DbManager */
    protected $auth;

    public function init()
    {
        parent::init();
        $this->auth = Yii::$app->authManager;
    }

    public function actionInit()
    {
        $auth = $this->auth;

        $auth->removeAll(); // ⚠ Removes all existing roles and permissions

        // Roles
        $owner = $auth->createRole('roleOwner');
        $auth->add($owner);

        $user = $auth->createRole('roleUser');
        $auth->add($user);

        $admin = $auth->createRole('roleAdmin');
        $auth->add($admin);
        $auth->addChild($admin, $owner);
        $auth->addChild($admin, $user);

        $superadmin = $auth->createRole('roleSuperadmin');
        $auth->add($superadmin);
        $auth->addChild($superadmin, $admin);

        // Create permissions
        $readOnlyData = $auth->createPermission('readOnlyData');
        $readOnlyData->description = 'View available data';
        $auth->add($readOnlyData);

        // $crateData = $auth->createPermission('crateData');
        // $crateData->description = 'Crate data';
        // $auth->add($crateData);

        // $updateData = $auth->createPermission('updateData');
        // $updateData->description = 'Update data';
        // $auth->add($updateData);

        // $deleteData = $auth->createPermission('deleteData');
        // $deleteData->description = 'Delete data';
        // $auth->add($deleteData);

        // Add permissions about pets
        $listPets = $auth->createPermission('listPets');
        $listPets->description = 'Get list of Pets';
        $auth->add($listPets);
        $auth->addChild($user, $listPets);

        $viewPet = $auth->createPermission('viewPet');
        $viewPet->description = 'View a specific Pet';
        $auth->add($viewPet);
        $auth->addChild($owner, $viewPet);

        $createPet = $auth->createPermission('createPet');
        $createPet->description = 'Create a specific Pet';
        $auth->add($createPet);
        $auth->addChild($owner, $createPet);

        $updatePet = $auth->createPermission('updatePet');
        $updatePet->description = 'Update a specific Pet';
        $auth->add($updatePet);
        $auth->addChild($admin, $updatePet);
        $auth->addChild($owner, $updatePet);

        $deletePet = $auth->createPermission('deletePet');
        $deletePet->description = 'Delete a specific Pet';
        $auth->add($deletePet);
        $auth->addChild($superadmin, $deletePet);
        $auth->addChild($owner, $deletePet);

        // Add owner rule
        $rule = new \app\rbac\OwnerRule();
        $auth->add($rule);

        // Add the "viewPetOwner" permission and associate it with the rule.
        $viewPetOwner = $auth->createPermission('viewPetOwner');
        $viewPetOwner->description = 'View own pet';
        $viewPetOwner->ruleName = $rule->name;
        $auth->add($viewPetOwner);

        $auth->addChild($viewPetOwner, $viewPet);
        $auth->addChild($owner, $viewPetOwner);

        // Add the "updatePetOwner" permission and associate it with the rule.
        $updatePetOwner = $auth->createPermission('updatePetOwner');
        $updatePetOwner->description = 'Update own pet';
        $updatePetOwner->ruleName = $rule->name;
        $auth->add($updatePetOwner);

        $auth->addChild($updatePetOwner, $updatePet);
        $auth->addChild($owner, $updatePetOwner);

        // Add the "deletePetOwner" permission and associate it with the rule.
        $deletePetOwner = $auth->createPermission('deletePetOwner');
        $deletePetOwner->description = 'Delete own pet';
        $deletePetOwner->ruleName = $rule->name;
        $auth->add($deletePetOwner);

        $auth->addChild($deletePetOwner, $deletePet);
        $auth->addChild($owner, $deletePetOwner);

        // Assign roles to users
        $user = $auth->getRole('roleUser');
        $owner = $auth->getRole('roleOwner');
        $admin = $auth->getRole('roleAdmin');
        $superadmin = $auth->getRole('roleSuperAdmin');
        $auth->assign($superadmin, 1); // User ID 1
        $auth->assign($admin, 2); // User ID 2
        $auth->assign($user, 5); // User ID 5
        $auth->assign($owner, 5); // User ID 5

    }
}
