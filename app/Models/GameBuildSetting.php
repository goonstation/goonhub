<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $server_id
 * @property string $branch
 * @property int $byond_major
 * @property int $byond_minor
 * @property string $rustg_version
 * @property bool $rp_mode
 * @property string|null $map_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\GameServer $gameServer
 * @property-read \App\Models\Map|null $map
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSetting filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSetting indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSetting newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSetting query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSetting whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSetting whereBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSetting whereByondMajor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSetting whereByondMinor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSetting whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSetting whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSetting whereMapId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSetting whereRpMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSetting whereRustgVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSetting whereServerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSetting whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class GameBuildSetting extends BaseModel
{
    use HasFactory;

    public function gameServer(): BelongsTo
    {
        return $this->belongsTo(GameServer::class, 'server_id', 'server_id');
    }

    public function map(): BelongsTo
    {
        return $this->belongsTo(Map::class, 'map_id', 'map_id');
    }
}
