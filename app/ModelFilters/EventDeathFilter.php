<?php

namespace App\ModelFilters;

class EventDeathFilter extends BaseModelFilter
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

    public function round($val)
    {
        return $this->where('round_id', $val);
    }

    public function mobName($val)
    {
        return $this->where('mob_name', 'ILIKE', '%'.$val.'%');
    }

    public function mobJob($val)
    {
        return $this->where('mob_job', 'ILIKE', '%'.$val.'%');
    }

    public function bruteloss($val)
    {
        return $this->filterRange('bruteloss', $val);
    }

    public function fireloss($val)
    {
        return $this->filterRange('fireloss', $val);
    }

    public function toxloss($val)
    {
        return $this->filterRange('toxloss', $val);
    }

    public function oxyloss($val)
    {
        return $this->filterRange('oxyloss', $val);
    }

    public function gibbed($val)
    {
        return $this->where('gibbed', $val);
    }

    public function lastWords($val)
    {
        return $this->where('last_words', 'ILIKE', '%'.$val.'%');
    }
}
