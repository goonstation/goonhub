<?php

namespace App\Models;

use App\Models\Traits\IndexFilterScope;
use EloquentFilter\Filterable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property int|null $current_team_id
 * @property string|null $profile_photo_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $two_factor_confirmed_at
 * @property bool $is_admin
 * @property int|null $game_admin_id
 * @property int|null $player_id
 * @property bool $passwordless
 * @property bool $emailless
 * @property-read \App\Models\Team|null $currentTeam
 * @property-read \App\Models\GameAdmin|null $gameAdmin
 * @property-read \App\Models\LinkedByondUser|null $linkedByond
 * @property-read \App\Models\LinkedDiscordUser|null $linkedDiscord
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team> $ownedTeams
 * @property-read int|null $owned_teams_count
 * @property-read \App\Models\Player|null $player
 * @property-read string $profile_photo_url
 * @property-read \App\Models\Membership|null $membership
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team> $teams
 * @property-read int|null $teams_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 *
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\User filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\User indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\User newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\User query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\User whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\User whereCurrentTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\User whereEmailless($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\User whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\User whereGameAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\User whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\User whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\User wherePasswordless($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\User wherePlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\User whereProfilePhotoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\User whereTwoFactorConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\User whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\User whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use Filterable;
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use IndexFilterScope;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'game_admin_id',
        'player_id',
        'passwordless',
        'emailless',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    protected function defaultProfilePhotoUrl()
    {
        // return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&color=ffd125&background=111';
        return '';
    }

    public function gameAdmin(): HasOne
    {
        return $this->hasOne(GameAdmin::class, 'id', 'game_admin_id');
    }

    public function player(): HasOne
    {
        return $this->hasOne(Player::class, 'id', 'player_id');
    }

    public function linkedByond(): HasOne
    {
        return $this->hasOne(LinkedByondUser::class);
    }

    public function linkedDiscord(): HasOne
    {
        return $this->hasOne(LinkedDiscordUser::class);
    }

    public function isAdmin()
    {
        return (bool) $this->is_admin;
    }

    public function isGameAdmin()
    {
        return (bool) $this->game_admin_id;
    }
}
