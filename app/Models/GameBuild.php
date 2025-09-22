<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $server_id
 * @property int|null $started_by
 * @property string|null $branch
 * @property string|null $commit
 * @property string|null $map_id
 * @property array<array-key, mixed>|null $test_merges
 * @property bool $failed
 * @property bool $cancelled
 * @property bool $map_switch
 * @property int|null $cancelled_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $ended_at
 * @property string|null $cancelled_reason
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\PlayerAdmin|null $cancelledBy
 * @property-read mixed $duration
 * @property-read \App\Models\GameServer $gameServer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GameBuildLog> $logs
 * @property-read int|null $logs_count
 * @property-read \App\Models\Map|null $map
 * @property-read \App\Models\PlayerAdmin|null $startedBy
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuild filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuild indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc', int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc', int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuild newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuild newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuild query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuild whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuild whereBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuild whereCancelled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuild whereCancelledBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuild whereCancelledReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuild whereCommit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuild whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuild whereEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuild whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuild whereFailed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuild whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuild whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuild whereMapId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuild whereMapSwitch($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuild whereServerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuild whereStartedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuild whereTestMerges($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuild whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class GameBuild extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'test_merges' => 'array',
        'ended_at' => 'datetime',
    ];

    protected $appends = [
        'duration',
    ];

    public static $auditingDisabled = true;

    protected function duration(): Attribute
    {
        return Attribute::make(
            get: function (mixed $val, array $attrs) {
                if (! array_key_exists('ended_at', $attrs)) {
                    return 0;
                }
                $start = new Carbon($attrs['created_at']);
                $end = new Carbon($attrs['ended_at']);

                return $start->diffInSeconds($end);
            },
        );
    }

    public function gameServer(): BelongsTo
    {
        return $this->belongsTo(GameServer::class, 'server_id', 'server_id');
    }

    public function startedBy(): BelongsTo
    {
        return $this->belongsTo(PlayerAdmin::class, 'started_by');
    }

    public function map(): BelongsTo
    {
        return $this->belongsTo(Map::class, 'map_id', 'map_id');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(PlayerAdmin::class, 'cancelled_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(GameBuildLog::class, 'build_id');
    }
}
