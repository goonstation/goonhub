<?php

namespace App\ModelFilters;

use App\ModelFilters\Common\HasTimestampFilters;
use EloquentFilter\ModelFilter;

class GameServerFilter extends ModelFilter
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
        return $this->where('invisible', $val);
    }
}
