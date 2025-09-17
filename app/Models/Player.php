<?php

namespace App\Models;

use App\Models\Events\EventDeath;
use App\Services\CommonRequest;
use App\Traits\HasOpenGraphData;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $ckey
 * @property string|null $key
 * @property string|null $byond_join_date
 * @property int|null $byond_major
 * @property int|null $byond_minor
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\PlayerBypassCap|null $bypassCap
 * @property-read mixed $can_bypass_cap
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PlayerConnection> $connections
 * @property-read int|null $connections_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\EventDeath> $deaths
 * @property-read int|null $deaths_count
 * @property-read \App\Models\PlayerConnection|null $firstConnection
 * @property-read \App\Models\PlayerAdmin|null $gameAdmin
 * @property-read mixed $has_imported_medals
 * @property-read \App\Models\PlayerHos|null $hos
 * @property-read \App\Models\PlayerMedalsImported|null $importedMedals
 * @property-read mixed $is_admin
 * @property-read mixed $is_hos
 * @property-read mixed $is_mentor
 * @property-read mixed $is_whitelisted
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\JobBan> $jobBans
 * @property-read int|null $job_bans_count
 * @property-read \App\Models\PlayerConnection|null $latestConnection
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PlayerMedal> $medals
 * @property-read int|null $medals_count
 * @property-read \App\Models\PlayerMentor|null $mentor
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PlayerNote> $notes
 * @property-read int|null $notes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PlayerParticipation> $participations
 * @property-read int|null $participations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PlayerParticipation> $participationsRp
 * @property-read int|null $participations_rp_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PlayerPlaytime> $playtime
 * @property-read int|null $playtime_count
 * @property-read \App\Models\User|null $user
 * @property-read \App\Models\VpnWhitelist|null $vpnWhitelist
 * @property-read \App\Models\PlayerWhitelist|null $whitelist
 *
 * @method static \Database\Factories\PlayerFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Player filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Player indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Player newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Player newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Player query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Player whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Player whereByondJoinDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Player whereByondMajor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Player whereByondMinor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Player whereCkey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Player whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Player whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Player whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Player whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Player whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Player whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Player extends BaseModel
{
    use HasFactory, HasOpenGraphData;

    const AUTH_SUFFIXES = [
        'byond' => '',
        'goonhub' => 'G',
        'discord' => 'D',
    ];

    protected $fillable = [
        'id',
        'ckey',
        'key',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'player_id');
    }

    public function gameAdmin(): HasOne
    {
        return $this->hasOne(PlayerAdmin::class, 'player_id');
    }

    public function connections(): HasMany
    {
        return $this->hasMany(PlayerConnection::class, 'player_id');
    }

    public function latestConnection(): HasOne
    {
        return $this->hasOne(PlayerConnection::class, 'player_id')->latestOfMany();
    }

    public function firstConnection(): HasOne
    {
        return $this->hasOne(PlayerConnection::class, 'player_id')->oldestOfMany();
    }

    public function participations(): HasMany
    {
        return $this->hasMany(PlayerParticipation::class, 'player_id');
    }

    public function participationsRp(): HasMany
    {
        return $this->hasMany(PlayerParticipation::class, 'player_id')
            ->where(function ($q) {
                $q->whereRelation('gameRound', 'rp_mode', true)
                    ->orWhere('legacy_data->rp_mode', 'true');
            });
    }

    public function playtime(): HasMany
    {
        return $this->hasMany(PlayerPlaytime::class, 'player_id');
    }

    public function deaths(): HasMany
    {
        return $this->hasMany(EventDeath::class, 'player_id');
    }

    public function vpnWhitelist(): HasOne
    {
        return $this->hasOne(VpnWhitelist::class, 'ckey', 'ckey');
    }

    public function jobBans(): HasMany
    {
        return $this->hasMany(JobBan::class, 'ckey', 'ckey');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(PlayerNote::class, 'player_id');
    }

    public function medals(): HasMany
    {
        return $this->hasMany(PlayerMedal::class);
    }

    public function importedMedals(): HasOne
    {
        return $this->hasOne(PlayerMedalsImported::class);
    }

    public function mentor(): HasOne
    {
        return $this->hasOne(PlayerMentor::class);
    }

    public function hos(): HasOne
    {
        return $this->hasOne(PlayerHos::class);
    }

    public function whitelist(): HasOne
    {
        return $this->hasOne(PlayerWhitelist::class);
    }

    public function bypassCap(): HasOne
    {
        return $this->hasOne(PlayerBypassCap::class);
    }

    /** @return Attribute<bool, never> */
    protected function isAdmin(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->gameAdmin()->exists(),
        );
    }

    /** @return Attribute<bool, never> */
    protected function hasImportedMedals(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->importedMedals()->exists(),
        );
    }

    /** @return Attribute<bool, never> */
    protected function isMentor(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->mentor()->exists()
        );
    }

    /** @return Attribute<bool, never> */
    protected function isHos(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->hos()->exists()
        );
    }

    /** @return Attribute<bool, never> */
    protected function isWhitelisted(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                return $this->isWhitelistedOnServer(app(CommonRequest::class)->fromServerId());
            }
        );
    }

    /** @return Attribute<bool, never> */
    protected function canBypassCap(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                return $this->canBypassCapOnServer(app(CommonRequest::class)->fromServerId());
            }
        );
    }

    public function isWhitelistedOnServer(string $serverId): bool
    {
        return $this->whitelist?->isWhitelisted($serverId) ?? false;
    }

    public function canBypassCapOnServer(string $serverId): bool
    {
        return $this->bypassCap?->canBypassCap($serverId) ?? false;
    }

    public static function getOpenGraphData(int $id): Player
    {
        $player = self::with([
            'playtime',
            'firstConnection',
        ])
            ->withCount([
                'participations',
                'participationsRp',
                'deaths',
            ])
            ->where('id', $id)
            ->firstOrFail();

        $totalSecondsPlayed = 0;
        foreach ($player->playtime as $playtime) {
            $totalSecondsPlayed += $playtime->seconds_played;
        }
        $player->setAttribute('hours_played', $totalSecondsPlayed / 3600);

        return $player;
    }
}
