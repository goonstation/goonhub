<?php

namespace App\ModelFilters;

use App\ModelFilters\Common\HasTimestampFilters;
use Carbon\Carbon;
use EloquentFilter\ModelFilter;

class PollFilter extends ModelFilter
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

    public function gameAdmin($val)
    {
        return $this->related('gameAdmin', function ($query) use ($val) {
            return $query->where('alias', 'ILIKE', '%'.$val.'%')
                ->orWhere('player.ckey', 'ILIKE', '%'.$val.'%');
        });
    }

    public function question($val)
    {
        return $this->whereLike('question', $val);
    }

    public function expiresAt($val)
    {
        return $this->filterDate('expires_at', $val);
    }

    public function active($val)
    {
        if ($val === 'true') {
            return $this->where('expires_at', null)
                ->orWhere('expires_at', '>', Carbon::now()->toDateTimeString());
        } else {
            return $this->where('expires_at', '!=', null)
                ->where('expires_at', '<', Carbon::now()->toDateTimeString());
        }
    }

    public function servers($val)
    {
        if (is_null($val)) {
            return $this->whereNull('servers');
        } elseif (is_array($val)) {
            $q = $this;
            foreach ($val as $server) {
                if (is_null($server) || $server === 'all') {
                    $q = $q->orWhereNull('servers');
                } else {
                    $q = $q->orWhereJsonContains('servers', $server);
                }
            }

            return $q;
        }
    }
}
