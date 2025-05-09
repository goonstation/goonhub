<?php

namespace App\Models;

use Carbon\Carbon;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
 * @property-read \App\Models\GameAdmin|null $deletedByGameAdmin
 * @property-read \App\Models\GameAdmin|null $gameAdmin
 * @property-read \App\Models\GameRound|null $gameRound
 * @property-read \App\Models\GameServer|null $gameServer
 * @property-read mixed $duration
 * @property-read mixed $duration_human
 *
 * @method static Builder<static>|JobBan filter(array $input = [], $filter = null)
 * @method static Builder<static>|JobBan newModelQuery()
 * @method static Builder<static>|JobBan newQuery()
 * @method static Builder<static>|JobBan onlyTrashed()
 * @method static Builder<static>|JobBan paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder<static>|JobBan query()
 * @method static Builder<static>|JobBan simplePaginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder<static>|JobBan whereBannedFromJob($value)
 * @method static Builder<static>|JobBan whereBeginsWith($column, $value, $boolean = 'and')
 * @method static Builder<static>|JobBan whereCkey($value)
 * @method static Builder<static>|JobBan whereCreatedAt($value)
 * @method static Builder<static>|JobBan whereDeletedAt($value)
 * @method static Builder<static>|JobBan whereDeletedBy($value)
 * @method static Builder<static>|JobBan whereEndsWith($column, $value, $boolean = 'and')
 * @method static Builder<static>|JobBan whereExpiresAt($value)
 * @method static Builder<static>|JobBan whereGameAdminId($value)
 * @method static Builder<static>|JobBan whereId($value)
 * @method static Builder<static>|JobBan whereLike($column, $value, $boolean = 'and')
 * @method static Builder<static>|JobBan whereReason($value)
 * @method static Builder<static>|JobBan whereRoundId($value)
 * @method static Builder<static>|JobBan whereServerId($value)
 * @method static Builder<static>|JobBan whereUpdatedAt($value)
 * @method static Builder<static>|JobBan withTrashed()
 * @method static Builder<static>|JobBan withoutTrashed()
 *
 * @mixin \Eloquent
 */
class JobBan extends BaseModel
{
    use Filterable, HasFactory, SoftDeletes;

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected $fillable = [
        'server_id',
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function gameServer()
    {
        return $this->belongsTo(GameServer::class, 'server_id', 'server_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function deletedByGameAdmin()
    {
        return $this->belongsTo(GameAdmin::class, 'deleted_by');
    }

    /**
     * @return Builder
     */
    public static function getValidJobBans(string $ckey, ?string $job = null, ?string $serverId = null)
    {
        $query = JobBan::with(['gameAdmin:id,ckey,name'])
            ->where('ckey', $ckey)
            ->where(function (Builder $builder) use ($serverId) {
                // Check if the ban applies to all servers, or the server id we were provided
                $builder->whereNull('server_id')
                    ->orWhere('server_id', $serverId);
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
