<?php

namespace App\ModelFilters;

class DiscordSettingFilter extends BaseModelFilter
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

    public function key($val)
    {
        return $this->where('key', 'ILIKE', '%'.$val.'%');
    }

    public function name($val)
    {
        return $this->where('name', 'ILIKE', '%'.$val.'%');
    }

    public function description($val)
    {
        return $this->where('description', 'ILIKE', '%'.$val.'%');
    }

    public function value($val)
    {
        return $this->where('value', 'ILIKE', '%'.$val.'%');
    }
}
