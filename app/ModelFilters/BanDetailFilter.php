<?php

namespace App\ModelFilters;

class BanDetailFilter extends BaseModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    public function ckey($val)
    {
        return $this->whereLike('ckey', $val);
    }

    public function comp($val)
    {
        return $this->whereLike('comp_id', $val);
    }

    public function ip($val)
    {
        return $this->whereLike('ip', $val);
    }

    public function player($val)
    {
        return $this->where('player_id', $val);
    }

    // removed boolean (deleted_at timestamp existance)
}
