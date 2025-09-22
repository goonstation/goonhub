<?php

namespace App\Models\Events;

use App\Models\GameRound;
use App\Models\Player;
use App\Traits\HasOpenGraphData;
use App\Traits\Voteable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $round_id
 * @property int|null $player_id
 * @property string|null $mob_name
 * @property string|null $mob_job
 * @property int|null $x
 * @property int|null $y
 * @property int|null $z
 * @property float|null $bruteloss
 * @property float|null $fireloss
 * @property float|null $toxloss
 * @property float|null $oxyloss
 * @property bool|null $gibbed
 * @property string|null $last_words
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\GameRound $gameRound
 * @property-read mixed $total_votes
 * @property-read \App\Models\Player|null $player
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Vote> $userVotes
 * @property-read int|null $user_votes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Vote> $votes
 * @property-read int|null $votes_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventDeath filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventDeath indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc', int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc', int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventDeath newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventDeath newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventDeath query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventDeath whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventDeath whereBruteloss($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventDeath whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventDeath whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventDeath whereFireloss($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventDeath whereGibbed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventDeath whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventDeath whereLastWords($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventDeath whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventDeath whereMobJob($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventDeath whereMobName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventDeath whereOxyloss($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventDeath wherePlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventDeath whereRoundId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventDeath whereToxloss($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventDeath whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventDeath whereX($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventDeath whereY($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventDeath whereZ($value)
 *
 * @mixin \Eloquent
 */
class EventDeath extends BaseEventModel
{
    use HasFactory, HasOpenGraphData, Voteable;

    protected $table = 'events_deaths';

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

    public static function getOpenGraphData(int $id)
    {
        return self::with([
            'gameRound',
            'gameRound.server',
        ])
            ->where('id', $id)
            ->whereRelation('gameRound', 'ended_at', '!=', null)
            ->firstOrFail();
    }
}
