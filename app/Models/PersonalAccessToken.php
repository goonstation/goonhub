<?php

namespace App\Models;

use App\Services\CommonRequest;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Laravel\Sanctum\Exceptions\MissingAbilityException;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

/**
 * @property int $id
 * @property string $tokenable_type
 * @property int $tokenable_id
 * @property string $name
 * @property string $token
 * @property array<array-key, mixed>|null $abilities
 * @property mixed|null $last_used_at
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $for_game_server
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GameServerGroup> $serverGroups
 * @property-read int|null $server_groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GameServer> $servers
 * @property-read int|null $servers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GameServer> $serversViaGroups
 * @property-read int|null $servers_via_groups_count
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $tokenable
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessToken query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessToken whereAbilities($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessToken whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessToken whereForGameServer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessToken whereLastUsedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessToken whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessToken whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessToken whereTokenableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessToken whereTokenableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessToken whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PersonalAccessToken extends SanctumPersonalAccessToken
{
    protected $fillable = [
        'name',
        'token',
        'abilities',
        'expires_at',
        'for_game_server',
    ];

    /**
     * Determine if the token has a given ability.
     *
     * @param  string  $ability
     * @return bool
     */
    public function can($ability)
    {
        $serverId = app(CommonRequest::class)->fromServerId();

        if ($serverId) {
            $isTokenForServer = $this->allServers()->where('server_id', $serverId)->exists();
            if (! $isTokenForServer) {
                throw new MissingAbilityException($ability, 'Token has no access to this server.');
            }
        }

        return in_array('*', $this->abilities) || array_key_exists($ability, array_flip($this->abilities));
    }

    protected function lastUsedAt(): Attribute
    {
        return Attribute::make(
            set: function (mixed $value): void {
                // disable updating the last_used_at attribute as it's not used
            },
        );
    }

    public function servers(): BelongsToMany
    {
        return $this->belongsToMany(GameServer::class, PersonalAccessTokenServer::class, 'personal_access_token_id', 'server_id')->withTimestamps();
    }

    public function serverGroups(): BelongsToMany
    {
        return $this->belongsToMany(GameServerGroup::class, PersonalAccessTokenServer::class, 'personal_access_token_id', 'server_group_id')->withTimestamps();
    }

    public function serversViaGroups(): HasManyThrough
    {
        return $this->hasManyThrough(
            GameServer::class,
            PersonalAccessTokenServer::class,
            'personal_access_token_id', // Foreign key on PersonalAccessTokenServer referencing PersonalAccessToken
            'group_id',        // Foreign key on GameServer referencing GameServerGroup
            'id',              // Local key on PersonalAccessToken
            'server_group_id'  // Local key on PersonalAccessTokenServer referencing group id
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
}
