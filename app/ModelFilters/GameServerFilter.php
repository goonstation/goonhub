<?php

namespace App\ModelFilters;

use Illuminate\Support\Facades\Auth;

class GameServerFilter extends BaseModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [
        'currentPlayersOnline' => [
            'player_count' => 'online',
        ],
        'currentRound' => [
            'current_round_id' => 'id',
        ],
    ];

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

    public function name($val)
    {
        return $this->where('name', 'ILIKE', '%'.$val.'%');
    }

    public function shortName($val)
    {
        return $this->where('short_name', 'ILIKE', '%'.$val.'%');
    }

    public function address($val)
    {
        return $this->where('address', 'ILIKE', '%'.$val.'%');
    }

    public function port($val)
    {
        return $this->where('port', $val);
    }

    public function active($val)
    {
        return $this->where('active', $val);
    }

    public function invisible($val)
    {
        if (Auth::user()?->isGameAdmin()) {
            return $this->where('invisible', $val);
        }

        return $this;
    }

    public function withInvisible($val)
    {
        if (Auth::user()?->isGameAdmin()) {
            return $this->where('invisible', true)->orWhere('invisible', false);
        }

        return $this;
    }

    public function groupId($val)
    {
        return $this->where('group_id', $val);
    }

    public function currentMap($val)
    {
        return $this->whereHas('currentRound.mapRecord', function ($query) use ($val) {
            $query->where('name', 'ILIKE', '%'.$val.'%');
        })
            ->orWhereHas('gameBuildSetting.map', function ($query) use ($val) {
                $query->where('name', 'ILIKE', '%'.$val.'%');
            });
    }
}
