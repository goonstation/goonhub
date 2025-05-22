<?php

namespace App\Models;

use EloquentFilter\Filterable;
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $ended_at
 * @property string|null $cancelled_reason
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\GameAdmin|null $cancelledBy
 * @property-read mixed $duration
 * @property-read \App\Models\GameServer $gameServer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GameBuildLog> $logs
 * @property-read int|null $logs_count
 * @property-read \App\Models\Map|null $map
 * @property-read \App\Models\GameAdmin|null $startedBy
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameBuild filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameBuild newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameBuild newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameBuild paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameBuild query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameBuild simplePaginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameBuild whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameBuild whereBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameBuild whereCancelled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameBuild whereCancelledBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameBuild whereCancelledReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameBuild whereCommit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameBuild whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameBuild whereEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameBuild whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameBuild whereFailed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameBuild whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameBuild whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameBuild whereMapId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameBuild whereMapSwitch($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameBuild whereServerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameBuild whereStartedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameBuild whereTestMerges($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameBuild whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class GameBuild extends BaseModel
{
    use Filterable, HasFactory;

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
        return $this->belongsTo(GameAdmin::class, 'started_by');
    }

    public function map(): BelongsTo
    {
        return $this->belongsTo(Map::class, 'map_id', 'map_id');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(GameAdmin::class, 'cancelled_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(GameBuildLog::class, 'build_id');
    }
}
