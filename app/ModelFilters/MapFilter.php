<?php

namespace App\ModelFilters;

class MapFilter extends BaseModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    public function map($val)
    {
        return $this->where('map_id', 'ILIKE', '%'.$val.'%');
    }

    public function name($val)
    {
        return $this->where('name', 'ILIKE', '%'.$val.'%');
    }

    public function lastBuiltBy($val)
    {
        return $this->related('gameAdmin', function ($query) use ($val) {
            return $query->where('name', 'ILIKE', '%'.$val.'%')
                ->orWhere('ckey', 'ILIKE', '%'.$val.'%');
        });
    }

    public function lastBuiltAt($val)
    {
        return $this->filterDate('last_built_at', $val);
    }
}
