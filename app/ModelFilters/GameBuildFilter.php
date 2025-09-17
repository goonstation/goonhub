<?php

namespace App\ModelFilters;

use App\ModelFilters\Common\HasTimestampFilters;
use EloquentFilter\ModelFilter;

class GameBuildFilter extends ModelFilter
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

    public function server($val)
    {
        return $this->where('server_id', $val);
    }

    public function startedBy($val)
    {
        return $this->related('startedBy', function ($query) use ($val) {
            return $query->where('alias', 'ILIKE', '%'.$val.'%')
                ->orWhere('player.ckey', 'ILIKE', '%'.$val.'%');
        });
    }

    public function branch($val)
    {
        return $this->where('branch', $val);
    }

    public function commit($val)
    {
        return $this->where('commit', $val);
    }

    public function mapId($val)
    {
        return $this->where('map_id', $val);
    }

    public function failed($val)
    {
        return $this->where('failed', $val);
    }

    public function cancelled($val)
    {
        return $this->where('cancelled', $val);
    }

    public function mapSwitch($val)
    {
        return $this->where('map_switch', $val);
    }

    public function success($val)
    {
        if ($val) {
            return $this->whereNotNull('ended_at')->where('cancelled', false)->where('failed', false);
        } else {
            return $this->where('cancelled', true)->orWhere('failed', true);
        }
    }

    public function building($val)
    {
        return $this->where('ended_at', $val ? '!=' : '=', null);
    }

    public function cancelledBy($val)
    {
        return $this->related('cancelledBy', function ($query) use ($val) {
            return $query->where('alias', 'ILIKE', '%'.$val.'%')
                ->orWhere('player.ckey', 'ILIKE', '%'.$val.'%');
        });
    }

    public function endedAt($val)
    {
        return $this->filterDate('ended_at', $val);
    }
}
