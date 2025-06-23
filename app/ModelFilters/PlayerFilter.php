<?php

namespace App\ModelFilters;

use App\ModelFilters\Common\HasRangeFilters;
use App\ModelFilters\Common\HasTimestampFilters;
use EloquentFilter\ModelFilter;

class PlayerFilter extends ModelFilter
{
    use HasRangeFilters, HasTimestampFilters;

    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    public function ckey($val)
    {
        return $this->where('ckey', 'ILIKE', '%'.$val.'%');
    }

    public function key($val)
    {
        return $this->where('key', 'ILIKE', '%'.$val.'%');
    }

    public function name($val)
    {
        return $this->where('ckey', 'ILIKE', '%'.$val.'%')
            ->orWhere('key', 'ILIKE', '%'.$val.'%');
    }

    public function connectionsCount($val)
    {
        return $this->filterRangeRelationship('connections', $val);
    }

    public function participationsCount($val)
    {
        return $this->filterRangeRelationship('participations', $val);
    }

    public function byondVersion($val)
    {
        $val = explode('.', $val);
        $major = $val[0];
        $minor = null;
        if (count($val) > 1) {
            $minor = $val[1];
        }

        $query = $this->where('byond_major', $major);
        if ($minor) {
            $query = $query->where('byond_minor', $minor);
        }

        return $query;
    }

    public function compId($val)
    {
        return $this->related('connections', function ($query) use ($val) {
            return $query->where('comp_id', $val);
        });
    }

    public function ip($val)
    {
        return $this->related('connections', function ($query) use ($val) {
            return $query->where('ip', $val);
        });
    }

    public function mentor($val)
    {
        return $val === 'true' || $val === '1' ? $this->whereHas('mentor') : $this->whereDoesntHave('mentor');
    }

    public function hos($val)
    {
        return $val === 'true' || $val === '1' ? $this->whereHas('hos') : $this->whereDoesntHave('hos');
    }

    public function whitelist($val)
    {
        return $val === 'true' || $val === '1' ? $this->whereHas('whitelist') : $this->whereDoesntHave('whitelist');
    }

    public function bypassCap($val)
    {
        return $val === 'true' || $val === '1' ? $this->whereHas('bypassCap') : $this->whereDoesntHave('bypassCap');
    }
}
