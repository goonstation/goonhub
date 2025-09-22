<?php

namespace App\ModelFilters;

class MedalFilter extends BaseModelFilter
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

    public function title($val)
    {
        return $this->where('title', 'ILIKE', '%'.$val.'%');
    }

    public function description($val)
    {
        return $this->where('description', 'ILIKE', '%'.$val.'%');
    }

    public function hidden($val)
    {
        return $this->where('hidden', '=', $val);
    }

    public function pmEarnedCount($val)
    {
        return $this->filterRange('pm.earned_count', $val);
    }
}
