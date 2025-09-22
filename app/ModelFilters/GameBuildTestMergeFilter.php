<?php

namespace App\ModelFilters;

class GameBuildTestMergeFilter extends BaseModelFilter
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

    public function pr($val)
    {
        return $this->where('pr_id', $val);
    }

    public function server($val)
    {
        return $this->related('buildSettings', function ($query) use ($val) {
            return $query->where('server_id', 'ILIKE', '%'.$val.'%');
        });
    }

    public function commit($val)
    {
        return $this->where('commit', $val);
    }

    public function addedBy($val)
    {
        return $this->related('addedBy', function ($query) use ($val) {
            return $query->where('name', 'ILIKE', '%'.$val.'%')
                ->orWhere('ckey', 'ILIKE', '%'.$val.'%');
        });
    }

    public function updatedBy($val)
    {
        return $this->related('updatedBy', function ($query) use ($val) {
            return $query->where('name', 'ILIKE', '%'.$val.'%')
                ->orWhere('ckey', 'ILIKE', '%'.$val.'%');
        });
    }
}
