<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $key
 * @property string $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $server_id
 * @property int|null $server_group_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\GameServer|null $server
 * @property-read \App\Models\GameServerGroup|null $serverGroup
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSecret filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSecret indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc', int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc', int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSecret newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSecret newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSecret query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSecret whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSecret whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSecret whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSecret whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSecret whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSecret whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSecret whereServerGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSecret whereServerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSecret whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSecret whereValue($value)
 *
 * @mixin \Eloquent
 */
class GameBuildSecret extends BaseModel
{
    protected $fillable = [
        'key',
        'value',
        'server_id',
        'server_group_id',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(GameServer::class, 'server_id');
    }

    public function serverGroup(): BelongsTo
    {
        return $this->belongsTo(GameServerGroup::class, 'server_group_id');
    }
}
