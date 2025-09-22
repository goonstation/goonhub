<?php

namespace App\ModelFilters;

class JobBanFilter extends BaseModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    public function round($val)
    {
        return $this->where('round_id', $val);
    }

    public function gameAdmin($val)
    {
        return $this->related('game_admins', function ($query) use ($val) {
            return $query->whereLike('ckey', $val);
        });
    }

    public function server($val)
    {
        return $this->where('server_id', $val);
    }

    public function ckey($val)
    {
        return $this->whereLike('ckey', $val);
    }

    public function bannedFromJob($val)
    {
        return $this->whereLike('banned_from_job', $val);
    }

    public function reason($val)
    {
        return $this->whereLike('reason', $val);
    }

    // public function expiredAt($val)
    // {
    //     $val = explode('-', $val);
    //     $from = date($val[0]);
    //     $to = date($val[1]);
    //     if (! $this->validateDate($from) || ! $this->validateDate($to)) {
    //         return;
    //     }

    //     return $this->whereBetween('expired_at', [$from, $to]);
    // }
}
