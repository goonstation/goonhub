<?php

namespace App\ModelFilters;

class EventTicketFilter extends BaseModelFilter
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

    public function target($val)
    {
        return $this->where('target', 'ILIKE', '%'.$val.'%');
    }

    public function reason($val)
    {
        return $this->where('reason', 'ILIKE', '%'.$val.'%');
    }

    public function issuer($val)
    {
        return $this->where('issuer', 'ILIKE', '%'.$val.'%');
    }
}
