<?php

namespace App\ModelFilters;

class RedirectFilter extends BaseModelFilter
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

    public function from($val)
    {
        return $this->where('from', 'ILIKE', '%'.$val.'%');
    }

    public function to($val)
    {
        return $this->where('to', 'ILIKE', '%'.$val.'%');
    }

    public function visits($val)
    {
        return $this->filterRange('visits', $val);
    }
}
