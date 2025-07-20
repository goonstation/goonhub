<?php

namespace App\Models\Events;

use App\Models\GameRound;
use App\Models\Player;
use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $round_id
 * @property int|null $player_id
 * @property string|null $objective
 * @property bool|null $success
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\GameRound $gameRound
 * @property-read \App\Models\Player|null $player
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntagObjective filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntagObjective indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntagObjective newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntagObjective newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntagObjective query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntagObjective whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntagObjective whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntagObjective whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntagObjective whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntagObjective whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntagObjective whereObjective($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntagObjective wherePlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntagObjective whereRoundId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntagObjective whereSuccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntagObjective whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class EventAntagObjective extends BaseEventModel
{
    use Compoships, HasFactory;

    protected $table = 'events_antag_objectives';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function gameRound()
    {
        return $this->belongsTo(GameRound::class, 'round_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function player()
    {
        return $this->belongsTo(Player::class, 'player_id');
    }
}
