<?php

namespace App\Models;

use App\Models\Traits\IndexFilterScope;
use App\Services\CommonRequest;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\Exceptions\MissingAbilityException;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property int $player_id
 * @property int|null $rank_id
 * @property string|null $alias
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $ckey
 * @property-read mixed $name
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \App\Models\Player|null $player
 * @property-read \App\Models\GameAdminRank|null $rank
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GameServerGroup> $serverGroups
 * @property-read int|null $server_groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GameServer> $servers
 * @property-read int|null $servers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GameServer> $serversViaGroups
 * @property-read int|null $servers_via_groups_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdmin filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdmin indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdmin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdmin newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdmin permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdmin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdmin role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdmin whereAlias($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdmin whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdmin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdmin whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdmin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdmin whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdmin wherePlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdmin whereRankId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdmin whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdmin withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PlayerAdmin withoutRole($roles, $guard = null)
 *
 * @mixin \Eloquent
 */
class PlayerAdmin extends Authenticatable
{
    use Filterable;
    use HasRoles;
    use IndexFilterScope;
    use Notifiable;

    protected string $guard_name = 'web';

    protected $fillable = [
        'player_id',
        'rank_id',
        'alias',
    ];

    public function checkPermissionTo($ability, $guardName = null)
    {
        $serverId = app(CommonRequest::class)->fromServerId();

        if ($serverId && ! $this->hasAccessToServer($serverId)) {
            throw new MissingAbilityException($ability, 'Admin has no access to this server.');
        }

        try {
            return $this->hasPermissionTo($ability, $guardName);
        } catch (PermissionDoesNotExist $e) {
            return false;
        }
    }

    protected function ckey(): Attribute
    {
        return Attribute::make(
            get: function (mixed $val, array $attrs) {
                return $this->player->ckey;
            },
        );
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: function (mixed $val, array $attrs) {
                return $attrs['alias'];
            },
        );
    }

    public function rank(): HasOne
    {
        return $this->hasOne(GameAdminRank::class, 'id', 'rank_id');
    }

    public function player(): HasOne
    {
        return $this->hasOne(Player::class, 'id', 'player_id');
    }

    public function servers(): BelongsToMany
    {
        return $this->belongsToMany(GameServer::class, PlayerAdminServer::class, 'player_admin_id', 'server_id')->withTimestamps();
    }

    public function serverGroups(): BelongsToMany
    {
        return $this->belongsToMany(GameServerGroup::class, PlayerAdminServer::class, 'player_admin_id', 'server_group_id')->withTimestamps();
    }

    public function serversViaGroups(): HasManyThrough
    {
        return $this->hasManyThrough(
            GameServer::class,
            PlayerAdminServer::class,
            'player_admin_id', // Foreign key on PlayerAdminServer referencing PlayerAdmin
            'group_id',        // Foreign key on GameServer referencing GameServerGroup
            'id',              // Local key on PlayerAdmin
            'server_group_id'  // Local key on PlayerAdminServer referencing group id
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

    public function hasAccessToServer(string $serverId): bool
    {
        return $this->allServers()->where('server_id', $serverId)->exists();
    }
}
