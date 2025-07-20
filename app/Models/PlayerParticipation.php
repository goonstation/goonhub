<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $player_id
 * @property int|null $round_id
 * @property string|null $job
 * @property string|null $legacy_data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\GameRound|null $gameRound
 * @property-read \App\Models\Player $player
 *
 * @method static \Database\Factories\PlayerParticipationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerParticipation filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerParticipation indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerParticipation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerParticipation newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerParticipation query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerParticipation whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerParticipation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerParticipation whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerParticipation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerParticipation whereJob($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerParticipation whereLegacyData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerParticipation whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerParticipation wherePlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerParticipation whereRoundId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerParticipation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PlayerParticipation extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'created_at',
        'updated_at',
    ];

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
