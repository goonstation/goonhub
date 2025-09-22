<?php

namespace App\Models\Events;

use App\Models\GameRound;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $round_id
 * @property string|null $names
 * @property int|null $score
 * @property int|null $highest_wave
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\GameRound $gameRound
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventGauntletHighScore filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventGauntletHighScore indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc', int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc', int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventGauntletHighScore newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventGauntletHighScore newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventGauntletHighScore query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventGauntletHighScore whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventGauntletHighScore whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventGauntletHighScore whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventGauntletHighScore whereHighestWave($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventGauntletHighScore whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventGauntletHighScore whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventGauntletHighScore whereNames($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventGauntletHighScore whereRoundId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventGauntletHighScore whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventGauntletHighScore whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class EventGauntletHighScore extends BaseEventModel
{
    use HasFactory;

    protected $table = 'events_gauntlet_high_scores';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function gameRound()
    {
        return $this->belongsTo(GameRound::class, 'round_id');
    }
}
