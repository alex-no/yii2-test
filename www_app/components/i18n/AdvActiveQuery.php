<?php
namespace app\components\i18n;

use yii\db\ActiveQuery;

class AdvActiveQuery extends ActiveQuery
{
    use LocalizedAttributeTrait;

    public function init()
    {
        parent::init();
        $this->initLocalizedTrait(); // вручную инициализируем трейт
    }

    public function select($columns, $option = null)
    {
        return parent::select($this->localizeColumns($columns), $option);
    }

    public function where($condition, $params = [])
    {
        return parent::where($this->localizeCondition($condition), $params);
    }

    public function andWhere($condition, $params = [])
    {
        return parent::andWhere($this->localizeCondition($condition), $params);
    }

    public function orWhere($condition, $params = [])
    {
        return parent::orWhere($this->localizeCondition($condition), $params);
    }

    // — можно добавить еще аналогичные методы, например: filterWhere, orderBy, groupBy и т.д.

    protected function localizeColumns($columns)
    {
        if (is_string($columns)) {
            $columns = preg_split('/\s*,\s*/', $columns);
        }

        if (!is_array($columns)) {
            return $columns;
        }

        $result = [];
        foreach ($columns as $key => $col) {
            if (is_string($key)) {
                $result[$this->getLocalizedAttributeName($key)] = $col;
            } else {
                $result[] = $this->getLocalizedAttributeName($col);
            }
        }

        return $result;
    }

    protected function localizeCondition($condition)
    {
        if (is_array($condition)) {
            $localized = [];
            foreach ($condition as $key => $value) {
                if (is_string($key)) {
                    $localized[$this->getLocalizedAttributeName($key)] = $value;
                } else {
                    $localized[$key] = $this->localizeCondition($value);
                }
            }
            return $localized;
        }
        return $condition;
    }
}
