<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $player_admin_id
 * @property int|null $server_id
 * @property int|null $server_group_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\PlayerAdmin $playerAdmin
 * @property-read \App\Models\GameServer|null $server
 * @property-read \App\Models\GameServerGroup|null $serverGroup
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdminServer filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdminServer indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc', int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc', int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdminServer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdminServer newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdminServer query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdminServer whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdminServer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdminServer whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdminServer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdminServer whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdminServer wherePlayerAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdminServer whereServerGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdminServer whereServerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdminServer whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PlayerAdminServer extends BaseModel
{
    protected $fillable = [
        'player_admin_id',
        'server_id',
        'server_group_id',
    ];

    public function playerAdmin(): BelongsTo
    {
        return $this->belongsTo(PlayerAdmin::class, 'player_admin_id');
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(GameServer::class, 'server_id');
    }

    public function serverGroup(): BelongsTo
    {
        return $this->belongsTo(GameServerGroup::class, 'server_group_id');
    }
}
