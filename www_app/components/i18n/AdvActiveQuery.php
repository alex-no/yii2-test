<?php

namespace app\components\i18n;

use yii\db\ActiveQuery;

class AdvActiveQuery extends ActiveQuery
{
    use LocalizedAttributeTrait;
}
