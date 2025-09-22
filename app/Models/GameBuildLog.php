<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $build_id
 * @property string|null $type
 * @property string|null $group
 * @property string $log
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\GameBuild $gameBuild
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildLog filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildLog indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc', int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc', int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildLog newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildLog query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildLog whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildLog whereBuildId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildLog whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildLog whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildLog whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildLog whereLog($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildLog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildLog whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class GameBuildLog extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'build_id',
        'log',
        'type',
        'group',
    ];

    public static $auditingDisabled = true;

    public function gameBuild(): BelongsTo
    {
        return $this->belongsTo(GameBuild::class, 'build_id');
    }
}
