<?php

namespace App\ModelFilters;

class EventAntagObjectiveFilter extends BaseModelFilter
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
}
