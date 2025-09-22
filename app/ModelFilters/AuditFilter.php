<?php

namespace App\ModelFilters;

class AuditFilter extends BaseModelFilter
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

    public function user($val)
    {
        return $this->where('user_id', $val);
    }

    public function event($val)
    {
        return $this->where('event', 'ILIKE', '%'.$val.'%');
    }

    public function auditableType($val)
    {
        return $this->where('auditable_type', "App\\Models\\$val");
    }
}
