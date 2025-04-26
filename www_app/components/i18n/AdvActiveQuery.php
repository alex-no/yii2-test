<?php
namespace app\components\i18n;

use yii\db\ActiveQuery;

class AdvActiveQuery extends ActiveQuery
{
    use LocalizedAttributeTrait;

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

    public function filterWhere($condition)
    {
        return parent::filterWhere($this->localizeCondition($condition));
    }

    public function andFilterWhere($condition)
    {
        return parent::andFilterWhere($this->localizeCondition($condition));
    }

    public function orFilterWhere($condition)
    {
        return parent::orFilterWhere($this->localizeCondition($condition));
    }

    public function orderBy($columns)
    {
        return parent::orderBy($this->localizeOrderColumns($columns));
    }

    public function addOrderBy($columns)
    {
        return parent::addOrderBy($this->localizeOrderColumns($columns));
    }

    public function groupBy($columns)
    {
        return parent::groupBy($this->localizeColumns($columns));
    }

    public function addGroupBy($columns)
    {
        return parent::addGroupBy($this->localizeColumns($columns));
    }

    // Converts strings/arrays of columns into localized names
    // In select: $columns = ['id', '@@name'] or ['name' => '@@name']
    protected function localizeColumns($columns)
    {
        $columns = $this->stringToArray($columns);

        $result = [];
        foreach ($columns as $key => $col) {
            if (is_string($key)) {
                $result[$key] = $this->getLocalizedAttributeName($col);
            } else {
                $result[] = $this->getLocalizedAttributeName($col);
            }
        }

        return $result;
    }

    // Converts strings/arrays of columns into localized names
    // In orderBy: $columns = ['@@name' => SORT_ASC] or ['@@name DESC', '@@title ASC']
    protected function localizeOrderColumns($columns)
    {
        $columns = $this->stringToArray($columns);

        $result = [];
        foreach ($columns as $key => $col) {
            if (is_string($key)) {
                $result[$this->getLocalizedAttributeName($key)] = $col;
            } else {
                $colParts = preg_split('/\s+/', $col);
                $localizedColumn = $this->getLocalizedAttributeName($colParts[0]);
                $result[$localizedColumn] = isset($colParts[1]) && strtoupper($colParts[1]) === 'DESC' ? SORT_DESC : SORT_ASC;
            }
        }

        return $result;
    }

    protected function stringToArray($columns)
    {
        $result = is_string($columns) ? preg_split('/\s*,\s*/', $columns) : $columns;
        if (!is_array($result)) {
            $result = [$result];
        }
        return $result;

    }

    // Converts a condition (in the format ['@@name' => 'Cat']) into a localized one
    protected function localizeCondition($condition)
    {
        if (is_array($condition)) {
            $localized = [];

            foreach ($condition as $key => $value) {
                if (is_string($key)) {
                    $localized[$this->getLocalizedAttributeName($key)] = $value;
                } elseif (is_array($value)) {
                    $localized[$key] = $this->localizeCondition($value);
                } else {
                    $localized[$key] = $value;
                }
            }
            return $localized;
        }
        return $condition;
    }
}
