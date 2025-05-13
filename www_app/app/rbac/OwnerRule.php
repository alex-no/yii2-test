<?php
namespace app\rbac;

use Yii;
use yii\rbac\Rule;

/**
 * Check if ownerID matches the user passed through the parameters
 */
class OwnerRule extends Rule
{
    /**
     * @var string the name of this rule
     */
    public $name = 'isOwner';

    /**
     * @var string the model class name
     */
    public $modelClass = 'app\models\PetOwner';

    /**
     * @param string|int $user the user ID.
     * @param Item $item the role or permission that this rule is associated width.
     * @param array $params parameters passed to ManagerInterface::checkAccess().
     * @return bool a value indicating whether the rule permits the role or permission it is associated with.
     */
    public function execute($user, $item, $params)
    {
        $model = $params['model'] ?? new $this->modelClass;
        if (empty($model->user_id)) {
            $id = $params['id'] ?? Yii::$app->request->get('id');
            if (empty($id)) {
                return false;
            }
            $model = $this->modelClass::findOne($id);
        }

        return !empty($model->user_id) && $model->user_id == $user;
    }
}
