<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int|null $game_admin_id
 * @property int|null $round_id
 * @property string|null $server_id
 * @property string $map
 * @property int $votes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\GameAdmin|null $gameAdmin
 * @property-read \App\Models\GameRound|null $gameRound
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MapSwitch filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MapSwitch indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MapSwitch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MapSwitch newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MapSwitch query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MapSwitch whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MapSwitch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MapSwitch whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MapSwitch whereGameAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MapSwitch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MapSwitch whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MapSwitch whereMap($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MapSwitch whereRoundId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MapSwitch whereServerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MapSwitch whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MapSwitch whereVotes($value)
 *
 * @mixin \Eloquent
 */
class MapSwitch extends BaseModel
{
    use HasFactory;

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
    public function gameAdmin()
    {
        return $this->belongsTo(GameAdmin::class, 'game_admin_id');
    }
}
