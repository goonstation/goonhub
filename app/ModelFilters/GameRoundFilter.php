<?php

namespace App\ModelFilters;

class GameRoundFilter extends BaseModelFilter
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

        return $this->where('server_id', 'ILIKE', '%'.$val.'%');
    }

    public function stationName($val)
    {
        return $this->related('latestStationName', function ($query) use ($val) {
            return $query->where('name', 'ILIKE', '%'.$val.'%');
        });
    }

    public function map($val)
    {
        return $this->where('map', 'ILIKE', '%'.$val.'%');
    }

    public function gameType($val)
    {
        return $this->where('game_type', 'ILIKE', '%'.$val.'%');
    }

    public function rpMode($val)
    {
        return $this->where('rp_mode', $val);
    }

    public function crashed($val)
    {
        return $this->where('crashed', $val);
    }

    public function endedAt($val)
    {
        return $this->filterDate('ended_at', $val);
    }
}
