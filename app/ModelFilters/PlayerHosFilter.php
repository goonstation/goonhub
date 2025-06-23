<?php

namespace App\ModelFilters;

use App\ModelFilters\Common\HasTimestampFilters;
use EloquentFilter\ModelFilter;

class PlayerHosFilter extends ModelFilter
{
    use HasTimestampFilters;

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

    public function player($val)
    {
        return $this->where('player_id', $val);
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
}
