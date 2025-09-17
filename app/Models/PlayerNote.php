<?php

namespace App\Models;

use App\Models\Traits\HasApiScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $player_id
 * @property string|null $ckey
 * @property int|null $game_admin_id
 * @property string|null $server_id
 * @property int|null $round_id
 * @property string $note
 * @property string|null $legacy_data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $server_group
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\PlayerAdmin|null $gameAdmin
 * @property-read \App\Models\GameRound|null $gameRound
 * @property-read \App\Models\GameServer|null $gameServer
 * @property-read \App\Models\GameServerGroup|null $gameServerGroup
 * @property-read \App\Models\Player|null $player
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerNote filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerNote forApi()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerNote indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerNote newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerNote query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerNote whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerNote whereCkey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerNote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerNote whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerNote whereGameAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerNote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerNote whereLegacyData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerNote whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerNote whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerNote wherePlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerNote whereRoundId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerNote whereServerGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerNote whereServerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerNote whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PlayerNote extends BaseModel
{
    use HasApiScope;
    use HasFactory;

    protected $fillable = [
        'game_admin_id',
        'player_id',
        'server_id',
        'server_group',
        'ckey',
        'note',
    ];

    public function gameRound(): BelongsTo
    {
        return $this->belongsTo(GameRound::class, 'round_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id');
    }

    public function gameAdmin(): BelongsTo
    {
        return $this->belongsTo(PlayerAdmin::class, 'game_admin_id');
    }

    public function gameServer(): BelongsTo
    {
        return $this->belongsTo(GameServer::class, 'server_id', 'server_id');
    }

    public function gameServerGroup(): BelongsTo
    {
        return $this->belongsTo(GameServerGroup::class, 'server_group', 'id');
    }

    public function allServers()
    {
        return GameServer::query()
            ->where(function ($query) {
                $query->whereIn('id', $this->gameServer()->select('game_servers.id'))
                    ->orWhereIn('group_id', $this->gameServerGroup()->select('game_server_groups.id'));
            });
    }
}
