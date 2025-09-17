<?php

namespace App\ModelFilters;

use App\ModelFilters\Common\HasTimestampFilters;
use EloquentFilter\ModelFilter;

class PlayerNoteFilter extends ModelFilter
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
        if ($this->input('exact')) {
            return $this->whereHas('player', function ($query) use ($val) {
                return $query->where('ckey', $val);
            })->orWhere('ckey', $val);
        } else {
            return $this->whereHas('player', function ($query) use ($val) {
                return $query->where('ckey', 'ILIKE', '%'.$val.'%');
            })->orWhere('ckey', 'ILIKE', '%'.$val.'%');
        }
    }

    public function gameAdmin($val)
    {
        return $this->related('gameAdmin', function ($query) use ($val) {
            return $query->where('alias', 'ILIKE', '%'.$val.'%')
                ->orWhere('player.ckey', 'ILIKE', '%'.$val.'%');
        });
    }

    public function server($val)
    {
        return $this->where('server_id', $val);
    }

    public function round($val)
    {
        return $this->where('round_id', $val);
    }

    public function note($val)
    {
        return $this->whereLike('note', $val);
    }
}
