<?php

namespace App\Models;

/**
 * @property int $id
 * @property int $player_bypass_cap_id
 * @property int $server_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\PlayerBypassCap $playerBypassCap
 * @property-read \App\Models\GameServer $server
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerBypassCapServer filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerBypassCapServer indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerBypassCapServer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerBypassCapServer newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerBypassCapServer query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerBypassCapServer whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerBypassCapServer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerBypassCapServer whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerBypassCapServer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerBypassCapServer whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerBypassCapServer wherePlayerBypassCapId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerBypassCapServer whereServerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerBypassCapServer whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PlayerBypassCapServer extends BaseModel
{
    protected $fillable = [
        'server_id',
    ];

    public function playerBypassCap()
    {
        return $this->belongsTo(PlayerBypassCap::class, 'player_bypass_cap_id');
    }

    public function server()
    {
        return $this->belongsTo(GameServer::class, 'server_id');
    }
}
