<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $map_id
 * @property int $layer_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\Map $layer
 * @property-read \App\Models\Map $map
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MapLayer filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MapLayer indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MapLayer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MapLayer newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MapLayer query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MapLayer whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MapLayer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MapLayer whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MapLayer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MapLayer whereLayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MapLayer whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MapLayer whereMapId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MapLayer whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class MapLayer extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'map_id',
        'layer_id',
    ];

    public function map()
    {
        return $this->belongsTo(Map::class, 'map_id');
    }

    public function layer()
    {
        return $this->belongsTo(Map::class, 'layer_id');
    }
}
