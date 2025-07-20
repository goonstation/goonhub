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
 * @property string|null $target
 * @property string|null $reason
 * @property string|null $issuer
 * @property string|null $issuer_job
 * @property string|null $issuer_ckey
 * @property int|null $amount
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventFine filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventFine indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventFine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventFine newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventFine query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventFine whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventFine whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventFine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventFine whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventFine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventFine whereIssuer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventFine whereIssuerCkey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventFine whereIssuerJob($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventFine whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventFine wherePlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventFine whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventFine whereRoundId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventFine whereTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventFine whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class EventFine extends BaseEventModel
{
    use HasFactory, HasOpenGraphData, Voteable;

    protected $table = 'events_fines';

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
