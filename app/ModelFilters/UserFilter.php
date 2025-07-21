<?php

namespace App\ModelFilters;

use App\ModelFilters\Common\HasTimestampFilters;
use EloquentFilter\ModelFilter;

class UserFilter extends ModelFilter
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

    public function name($val)
    {
        return $this->where('name', 'ILIKE', '%'.$val.'%');
    }

    public function email($val)
    {
        return $this->where('email', 'ILIKE', '%'.$val.'%');
    }

    public function passwordless($val)
    {
        return $this->where('passwordless', $val);
    }

    public function emailless($val)
    {
        return $this->where('emailless', $val);
    }
}
