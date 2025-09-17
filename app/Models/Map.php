<?php

namespace App\Models;

use App\Observers\MapObserver;
use App\Traits\HasOpenGraphData;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $map_id
 * @property string $name
 * @property bool $active
 * @property bool $is_layer
 * @property int $tile_width
 * @property int $tile_height
 * @property string|null $last_built_at
 * @property int|null $last_built_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $admin_only
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\PlayerAdmin|null $gameAdmin
 * @property-read \App\Models\GameRound|null $latestGameRound
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Map> $layers
 * @property-read int|null $layers_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Map filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Map indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Map newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Map newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Map query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Map whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Map whereAdminOnly($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Map whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Map whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Map whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Map whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Map whereIsLayer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Map whereLastBuiltAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Map whereLastBuiltBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Map whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Map whereMapId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Map whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Map whereTileHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Map whereTileWidth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Map whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[ObservedBy([MapObserver::class])]
class Map extends BaseModel
{
    use HasFactory, HasOpenGraphData, Notifiable;

    const PUBLIC_ROOT = 'app/public/maps';

    const PRIVATE_ROOT = 'app/private-maps';

    protected $casts = [
        'last_updated_at' => 'datetime',
    ];

    public function latestGameRound(): HasOne
    {
        return $this->hasOne(GameRound::class, 'map', 'map_id')->latest();
    }

    public function gameAdmin(): BelongsTo
    {
        return $this->belongsTo(PlayerAdmin::class, 'last_built_by');
    }

    public function layers(): HasManyThrough
    {
        return $this->hasManyThrough(Map::class, MapLayer::class, 'map_id', 'id', 'id', 'layer_id');
    }

    public static function getOpenGraphData(int $id)
    {
        $map = self::where('id', $id)
            ->where('active', true)
            ->where('is_layer', false)
            ->where('admin_only', false)
            ->firstOrFail();
        $map->setAttribute('thumb_path', storage_path('app/public/maps/'.strtolower($map->map_id).'/thumb.png'));

        return $map;
    }
}
