<?php

namespace App\ModelFilters;

class EventAntagFilter extends BaseModelFilter
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

    public function mobName($val)
    {
        return $this->where('mob_name', 'ILIKE', '%'.$val.'%');
    }

    public function mobJob($val)
    {
        return $this->where('mob_job', 'ILIKE', '%'.$val.'%');
    }

    public function traitorType($val)
    {
        return $this->where('traitor_type', 'ILIKE', '%'.$val.'%');
    }

    public function success($val)
    {
        return $this->where('success', $val);
    }
}
