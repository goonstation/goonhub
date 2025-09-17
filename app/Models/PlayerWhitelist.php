<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @property int $id
 * @property int $player_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\Player $player
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GameServerGroup> $serverGroups
 * @property-read int|null $server_groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GameServer> $servers
 * @property-read int|null $servers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GameServer> $serversViaGroups
 * @property-read int|null $servers_via_groups_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerWhitelist filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerWhitelist indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerWhitelist newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerWhitelist newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerWhitelist query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerWhitelist whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerWhitelist whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerWhitelist whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerWhitelist whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerWhitelist whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerWhitelist wherePlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerWhitelist whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PlayerWhitelist extends BaseModel
{
    protected $table = 'player_whitelist';

    protected $fillable = [
        'player_id',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id');
    }

    public function servers(): BelongsToMany
    {
        return $this->belongsToMany(GameServer::class, PlayerWhitelistServer::class, 'player_whitelist_id', 'server_id')->withTimestamps();
    }

    public function serverGroups(): BelongsToMany
    {
        return $this->belongsToMany(GameServerGroup::class, PlayerWhitelistServer::class, 'player_whitelist_id', 'server_group_id')->withTimestamps();
    }

    public function serversViaGroups(): HasManyThrough
    {
        return $this->hasManyThrough(
            GameServer::class,
            PlayerWhitelistServer::class,
            'player_whitelist_id', // Foreign key on PlayerWhitelistServer referencing PlayerWhitelist
            'group_id',        // Foreign key on GameServer referencing GameServerGroup
            'id',              // Local key on PlayerWhitelist
            'server_group_id'  // Local key on PlayerWhitelistServer referencing group id
        )->whereNotNull('server_group_id');
    }

    public function allServers()
    {
        return GameServer::query()
            ->where(function ($query) {
                $query->whereIn('id', $this->servers()->select('game_servers.id'))
                    ->orWhereIn('group_id', $this->serverGroups()->select('game_server_groups.id'));
            });
    }

    public function isWhitelisted(string $serverId): bool
    {
        return $this->allServers()->where('server_id', $serverId)->exists();
    }

    public function addServer(GameServer $server)
    {
        if ($this->servers()->where('server_id', $server->id)->exists()) {
            return;
        }

        $this->servers()->attach($server->id);
    }

    public function addServerGroup(GameServerGroup $serverGroup)
    {
        if ($this->serverGroups()->where('server_group_id', $serverGroup->id)->exists()) {
            return;
        }

        $this->serverGroups()->attach($serverGroup->id);
    }
}
