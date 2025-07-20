<?php

namespace App\Models;

use Carbon\Carbon;
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\JobBan filter(array $input = [], $filter = null)
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
    use HasFactory, SoftDeletes;

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
