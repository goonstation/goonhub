<?php

namespace App\ModelFilters;

class PlayerAdminFilter extends BaseModelFilter
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

    public function ckey($val)
    {
        return $this->related('player', function ($query) use ($val) {
            return $query->where('ckey', 'ILIKE', '%'.$val.'%');
        });
    }

    public function key($val)
    {
        return $this->related('player', function ($query) use ($val) {
            return $query->where('key', 'ILIKE', '%'.$val.'%');
        });
    }

    public function alias($val)
    {
        return $this->where('alias', 'ILIKE', '%'.$val.'%');
    }

    public function rank($val)
    {
        return $this->where('rank_id', $val);
    }
}
