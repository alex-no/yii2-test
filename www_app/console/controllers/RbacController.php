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
        $user = $auth->createRole('roleUser');
        $auth->add($user);

        $admin = $auth->createRole('roleAdmin');
        $auth->add($admin);
        $auth->addChild($admin, $user);

        $superadmin = $auth->createRole('roleSuperadmin');
        $auth->add($superadmin);
        $auth->addChild($superadmin, $admin);

        // Add owner rule
        $rule = new \app\rbac\OwnerRule();
        $auth->add($rule);

        $petOwner = $auth->createPermission('petOwner');
        $petOwner->description = 'Is owner of a pet';
        $petOwner->ruleName = $rule->name;
        $auth->add($petOwner);

        // Link permission to role
        $auth->addChild($user, $petOwner);

        // Assign roles to users
        // $auth->assign($superadmin, 1); // User ID 1
        // $auth->assign($admin, 2); // User ID 2
        // $auth->assign($user, 5); // User ID 5
    }
}
