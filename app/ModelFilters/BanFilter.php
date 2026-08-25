<?php

namespace App\ModelFilters;

use Illuminate\Support\Carbon;

class BanFilter extends BaseModelFilter
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

    public function server($val)
    {
        return $this->where('server_id', $val);
    }

    public function serverGroup($val)
    {
        return $this->where('server_group', $val);
    }

    public function adminCkey($val)
    {
        return $this->related('gameAdmin', function ($query) use ($val) {
            return $query->where('name', 'ILIKE', '%'.$val.'%')
                ->orWhere('ckey', 'ILIKE', '%'.$val.'%');
        });
    }

    public function reason($val)
    {
        return $this->where('reason', 'ILIKE', '%'.$val.'%');
    }

    public function originalBanCkey($val)
    {
        return $this->related('originalBanDetail', function ($query) use ($val) {
            return $query->where('ckey', 'ILIKE', '%'.$val.'%');
        });
    }

    public function ckey($val)
    {
        return $this->related('details', function ($query) use ($val) {
            return $query->where('ckey', 'ILIKE', '%'.$val.'%');
        });
    }

    public function compId($val)
    {
        return $this->related('details', function ($query) use ($val) {
            return $query->where('comp_id', 'ILIKE', '%'.$val.'%');
        });
    }

    public function ip($val)
    {
        return $this->related('details', function ($query) use ($val) {
            return $query->where('ip', '<<', $val)
                ->orWhere('ip', $val);
        });
    }

    public function requiresAppeal($val)
    {
        return $this->where('requires_appeal', '=', $val);
    }

    public function active($val)
    {
        if ($val === 'true' || $val === '1') {
            return $this->whereNull('deleted_at')->where(function ($query) {
                return $query->where('expires_at', '>', Carbon::now())
                    ->orWhere('expires_at', null);
            });
        } else {
            return $this->whereNotNull('deleted_at')->orWhere(function ($query) {
                return $query->whereNotNull('expires_at')->where('expires_at', '<', Carbon::now());
            });
        }
    }

    public function permanent($val)
    {
        if ($val === 'true' || $val === '1') {
            return $this->whereNull('expires_at');
        } else {
            return $this->whereNotNull('expires_at');
        }
    }

    public function details($val)
    {
        return $this->filterRangeRelationship('details', $val);
    }

    public function expiresAt($val)
    {
        return $this->filterDate('expires_at', $val);
    }

    public function deletedAt($val)
    {
        return $this->filterDate('deleted_at', $val);
    }
}
