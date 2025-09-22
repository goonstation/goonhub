<?php

namespace App\ModelFilters;

class GameBuildSettingFilter extends BaseModelFilter
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

    public function branch($val)
    {
        return $this->where('branch', $val);
    }

    public function byondMajor($val)
    {
        return $this->filterRange('byond_major', $val);
    }

    public function byondMinor($val)
    {
        return $this->filterRange('byond_minor', $val);
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

    public function rustgVersion($val)
    {
        return $this->where('rustg_version', 'ILIKE', '%'.$val.'%');
    }

    public function rpMode($val)
    {
        return $this->where('rp_mode', $val);
    }

    public function mapId($val)
    {
        return $this->where('map_id', $val);
    }

    public function map($val)
    {
        return $this->related('map', function ($query) use ($val) {
            return $query->where('name', 'ILIKE', '%'.$val.'%');
        });
    }
}
