<?php

namespace App\Models;

/**
 * @property int $id
 * @property int $player_whitelist_id
 * @property int $server_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\PlayerWhitelist $playerWhitelist
 * @property-read \App\Models\GameServer $server
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerWhitelistServer filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerWhitelistServer indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerWhitelistServer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerWhitelistServer newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerWhitelistServer query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerWhitelistServer whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerWhitelistServer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerWhitelistServer whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerWhitelistServer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerWhitelistServer whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerWhitelistServer wherePlayerWhitelistId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerWhitelistServer whereServerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerWhitelistServer whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PlayerWhitelistServer extends BaseModel
{
    protected $fillable = [
        'server_id',
    ];

    public function playerWhitelist()
    {
        return $this->belongsTo(PlayerWhitelist::class, 'player_whitelist_id');
    }

    public function server()
    {
        return $this->belongsTo(GameServer::class, 'server_id');
    }
}
