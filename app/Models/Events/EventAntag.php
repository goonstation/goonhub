<?php

namespace App\Models\Events;

use App\Models\GameRound;
use App\Models\Player;
use App\Traits\HasOpenGraphData;
use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $round_id
 * @property int|null $player_id
 * @property string|null $mob_name
 * @property string|null $mob_job
 * @property string|null $traitor_type
 * @property string|null $special
 * @property string|null $late_joiner
 * @property bool|null $success
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\GameRound $gameRound
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\EventAntagItemPurchase> $itemPurchases
 * @property-read int|null $item_purchases_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\EventAntagObjective> $objectives
 * @property-read int|null $objectives_count
 * @property-read \App\Models\Player|null $player
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntag filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntag indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc', int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc', int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntag newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntag query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntag whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntag whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntag whereLateJoiner($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntag whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntag whereMobJob($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntag whereMobName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntag wherePlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntag whereRoundId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntag whereSpecial($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntag whereSuccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntag whereTraitorType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventAntag whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class EventAntag extends BaseEventModel
{
    use Compoships, HasFactory, HasOpenGraphData;

    protected $table = 'events_antags';

    public function gameRound(): BelongsTo
    {
        return $this->belongsTo(GameRound::class, 'round_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id');
    }

    public function objectives(): HasMany
    {
        return $this->hasMany(
            EventAntagObjective::class,
            ['player_id', 'round_id'],
            ['player_id', 'round_id']
        );
    }

    public function itemPurchases(): HasMany
    {
        return $this->hasMany(
            EventAntagItemPurchase::class,
            ['player_id', 'round_id'],
            ['player_id', 'round_id']
        );
    }

    public static function getOpenGraphData(int $id)
    {
        return self::with([
            'gameRound',
            'gameRound.server',
            'objectives',
            'itemPurchases',
        ])
            ->where('id', $id)
            ->whereRelation('gameRound', 'ended_at', '!=', null)
            ->firstOrFail();
    }
}
