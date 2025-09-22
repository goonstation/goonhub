<?php

namespace App\ModelFilters;

class EventErrorFilter extends BaseModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    public function id($val)
    {
        return $this->where('id', $val);
    }

    public function round($val)
    {
        return $this->where('round_id', $val);
    }

    public function count($val)
    {
        return $this->filterRange('count', $val);
    }

    public function overviewCount($val)
    {
        return $this->filterRangeHaving('sum', 'count', $val);
    }

    public function roundCount($val)
    {
        return $this->filterRange('round_count', $val);
    }

    public function overviewRoundCount($val)
    {
        return $this->filterRangeHaving('sum', 'round_count', $val);
    }

    public function name($val)
    {
        return $this->where('name', 'ILIKE', '%'.$val.'%');
    }

    public function file($val)
    {
        return $this->where('file', 'ILIKE', '%'.$val.'%');
    }

    public function line($val)
    {
        return $this->where('line', $val);
    }
}
