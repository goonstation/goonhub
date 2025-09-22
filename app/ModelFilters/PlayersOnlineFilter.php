<?php

namespace App\ModelFilters;

class PlayersOnlineFilter extends BaseModelFilter
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

    public function server($val)
    {
        if ($val === 'all') {
            return;
        }

        return $this->where('server_id', $val);
    }

    public function online($val)
    {
        return $this->filterRange('online', $val);
    }
}
