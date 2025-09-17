<?php

namespace App\Models;

use App\Models\Traits\HasApiScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int|null $round_id
 * @property int|null $game_admin_id
 * @property string|null $server_id
 * @property string $ckey
 * @property string $banned_from_job
 * @property string|null $reason
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $deleted_by
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\PlayerAdmin|null $deletedByGameAdmin
 * @property-read \App\Models\PlayerAdmin|null $gameAdmin
 * @property-read \App\Models\GameRound|null $gameRound
 * @property-read \App\Models\GameServer|null $gameServer
 * @property-read \App\Models\GameServerGroup|null $gameServerGroup
 * @property-read mixed $duration
 * @property-read mixed $duration_human
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\JobBan filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\JobBan forApi()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\JobBan indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\JobBan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\JobBan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\JobBan onlyTrashed()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\JobBan query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\JobBan whereBannedFromJob($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\JobBan whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\JobBan whereCkey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\JobBan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\JobBan whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\JobBan whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\JobBan whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\JobBan whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\JobBan whereGameAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\JobBan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\JobBan whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\JobBan whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\JobBan whereRoundId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\JobBan whereServerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\JobBan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\JobBan withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\JobBan withoutTrashed()
 *
 * @mixin \Eloquent
 */
class JobBan extends BaseModel
{
    use HasApiScope, HasFactory, SoftDeletes;

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected $fillable = [
        'server_id',
        'server_group',
        'reason',
        'banned_from_job',
        'expires_at',
    ];

    protected $appends = ['duration', 'duration_human'];

    public function getDurationAttribute()
    {
        $now = Carbon::now();
        if (! $this->expires_at || $now->isAfter($this->expires_at)) {
            return 0;
        }

        return $now->diffInSeconds($this->expires_at);
    }

    public function getDurationHumanAttribute()
    {
        if (! $this->expires_at) {
            return null;
        }

        return $this->expires_at->longAbsoluteDiffForHumans(parts: 99);
    }

    public function gameRound(): BelongsTo
    {
        return $this->belongsTo(GameRound::class, 'round_id');
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

    public function deletedByGameAdmin(): BelongsTo
    {
        return $this->belongsTo(PlayerAdmin::class, 'deleted_by');
    }

    public static function getValidJobBans(string $ckey, ?string $job = null, ?string $serverId = null, ?int $serverGroupId = null): Builder
    {
        $query = JobBan::query()
            ->where('ckey', $ckey)
            ->where(function (Builder $query) use ($serverId, $serverGroupId) {
                // Check if the ban applies to all servers, or the server id we were provided
                $query->whereNull(['server_id', 'server_group']);

                if ($serverId) {
                    $query->orWhere('server_id', $serverId);
                }

                if ($serverGroupId) {
                    $query->orWhere('server_group', $serverGroupId);
                }
            })
            ->where(function (Builder $builder) {
                // Check the ban is permanent, or has yet to expire
                $builder->whereNull('expires_at')
                    ->orWhere('expires_at', '>', Carbon::now()->toDateTimeString());
            });

        if ($job) {
            $query = $query->where('banned_from_job', $job);
        }

        return $query;
    }
}
