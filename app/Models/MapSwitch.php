<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
 * @property-read \App\Models\PlayerAdmin|null $gameAdmin
 * @property-read \App\Models\GameRound|null $gameRound
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MapSwitch filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\MapSwitch indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc', int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc', int $perPage = 15, bool $simple = false)
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

    public function gameRound(): BelongsTo
    {
        return $this->belongsTo(GameRound::class, 'round_id');
    }

    public function gameAdmin(): BelongsTo
    {
        return $this->belongsTo(PlayerAdmin::class, 'game_admin_id');
    }
}
