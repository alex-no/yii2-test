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
        $this->auth->removeAll(); // ⚠ Removes all existing roles and permissions

        // Create permissions
        $manageUsers = $this->auth->createPermission('manageUsers');
        $manageUsers->description = 'Manage users';
        $this->auth->add($manageUsers);

        $manageDogs = $this->auth->createPermission('manageDogs');
        $manageDogs->description = 'Manage pets';
        $this->auth->add($manageDogs);

        $viewOwnDogs = $this->auth->createPermission('viewOwnDogs');
        $viewOwnDogs->description = 'View and edit own pets';
        $this->auth->add($viewOwnDogs);

        $readOnlyData = $this->auth->createPermission('readOnlyData');
        $readOnlyData->description = 'View available data';
        $this->auth->add($readOnlyData);

        // Roles
        $user = $this->auth->createRole('roleUser');
        $this->auth->add($user);
        $this->auth->addChild($user, $viewOwnDogs);
        $this->auth->addChild($user, $readOnlyData);

        $admin = $this->auth->createRole('roleAdmin');
        $this->auth->add($admin);
        $this->auth->addChild($admin, $manageDogs);
        $this->auth->addChild($admin, $readOnlyData);

        $superadmin = $this->auth->createRole('roleSuperadmin');
        $this->auth->add($superadmin);
        $this->auth->addChild($superadmin, $manageUsers);
        $this->auth->addChild($superadmin, $manageDogs);
        $this->auth->addChild($superadmin, $readOnlyData);
    }
}
