<?php

namespace App\ModelFilters;

class PlayerMedalFilter extends BaseModelFilter
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

    public function player($val)
    {
        return $this->where('player_id', $val);
    }

    public function ckey($val)
    {
        return $this->whereHas('player', function ($query) use ($val) {
            return $query->where('ckey', $val);
        });
    }

    public function medal($val)
    {
        return $this->where('player_id', $val);
    }
}
