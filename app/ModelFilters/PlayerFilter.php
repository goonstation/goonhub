<?php

namespace App\ModelFilters;

use App\Models\PlayerConnection;
use Illuminate\Support\Facades\DB;

class PlayerFilter extends BaseModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    public function ckey($val)
    {
        return $this->where('ckey', 'ILIKE', '%'.$val.'%');
    }

    public function key($val)
    {
        return $this->where('key', 'ILIKE', '%'.$val.'%');
    }

    public function name($val)
    {
        return $this->where('ckey', 'ILIKE', '%'.$val.'%')
            ->orWhere('key', 'ILIKE', '%'.$val.'%');
    }

    public function connectionsCount($val)
    {
        return $this->filterRangeRelationship('connections', $val);
    }

    public function sortByConnectionsCount($order)
    {
        return $this->orderBy('connections_count', $order);
    }

    public function participationsCount($val)
    {
        return $this->filterRangeRelationship('participations', $val);
    }

    public function sortByParticipationsCount($order)
    {
        return $this->orderBy('participations_count', $order);
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

    public function sortByByondVersion($order)
    {
        return $this->orderByRaw("byond_major $order NULLS LAST, byond_minor $order NULLS LAST");
    }

    public function compId($val)
    {
        return $this->related('connections', function ($query) use ($val) {
            return $query->where('comp_id', $val);
        });
    }

    public function sortByCompId($order)
    {
        return $this->orderByRaw(
            '('.PlayerConnection::select('comp_id')
                ->whereColumn('player_connections.player_id', 'players.id')
                ->latest()
                ->take(1)
                ->toSql().') '.
            $order.' NULLS LAST'
        );
    }

    public function ip($val)
    {
        return $this->related('connections', function ($query) use ($val) {
            return $query->where('ip', $val);
        });
    }

    public function sortByIp($order)
    {
        return $this->orderByRaw(
            '('.PlayerConnection::select('ip')
                ->whereColumn('player_connections.player_id', 'players.id')
                ->latest()
                ->take(1)
                ->toSql().') '.
            $order.' NULLS LAST'
        );
    }

    public function mentor($val)
    {
        return $val === 'true' || $val === '1' ? $this->whereHas('mentor') : $this->whereDoesntHave('mentor');
    }

    public function sortByMentor($order)
    {
        return $this->leftJoin('player_mentors', 'players.id', '=', 'player_mentors.player_id')
            ->orderBy(DB::raw('CASE WHEN player_mentors.id IS NOT NULL THEN 1 ELSE 0 END'), $order);
    }

    public function hos($val)
    {
        return $val === 'true' || $val === '1' ? $this->whereHas('hos') : $this->whereDoesntHave('hos');
    }

    public function sortByHos($order)
    {
        return $this->leftJoin('player_hos', 'players.id', '=', 'player_hos.player_id')
            ->orderBy(DB::raw('CASE WHEN player_hos.id IS NOT NULL THEN 1 ELSE 0 END'), $order);
    }

    public function whitelist($val)
    {
        return $val === 'true' || $val === '1' ? $this->whereHas('whitelist') : $this->whereDoesntHave('whitelist');
    }

    public function sortByWhitelist($order)
    {
        return $this->leftJoin('player_whitelist', 'players.id', '=', 'player_whitelist.player_id')
            ->orderBy(DB::raw('CASE WHEN player_whitelist.id IS NOT NULL THEN 1 ELSE 0 END'), $order);
    }

    public function bypassCap($val)
    {
        return $val === 'true' || $val === '1' ? $this->whereHas('bypassCap') : $this->whereDoesntHave('bypassCap');
    }

    public function sortByBypassCap($order)
    {
        return $this->leftJoin('player_bypass_cap', 'players.id', '=', 'player_bypass_cap.player_id')
            ->orderBy(DB::raw('CASE WHEN player_bypass_cap.id IS NOT NULL THEN 1 ELSE 0 END'), $order);
    }
}
