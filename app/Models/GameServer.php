<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $server_id
 * @property string $name
 * @property string $short_name
 * @property string $address
 * @property int $port
 * @property bool $active
 * @property bool $invisible
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $orchestrator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\PlayersOnline|null $currentPlayersOnline
 * @property-read \App\Models\GameRound|null $currentRound
 * @property-read \App\Models\GameBuildSetting|null $gameBuildSetting
 * @property-read mixed $byond_link
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameServer filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameServer indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameServer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameServer newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameServer query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameServer whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameServer whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameServer whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameServer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameServer whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameServer whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameServer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameServer whereInvisible($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameServer whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameServer whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameServer whereOrchestrator($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameServer wherePort($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameServer whereServerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameServer whereShortName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameServer whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class GameServer extends BaseModel
{
    use HasFactory;

    protected $appends = ['byond_link'];

    public function getByondLinkAttribute()
    {
        return 'byond://'.$this->address.':'.$this->port;
    }

    public function gameBuildSetting(): HasOne
    {
        return $this->hasOne(GameBuildSetting::class, 'server_id', 'server_id');
    }

    public function currentPlayersOnline(): HasOne
    {
        return $this->hasOne(PlayersOnline::class, 'server_id', 'server_id')->latestOfMany();
    }

    public function currentRound(): HasOne
    {
        return $this->hasOne(GameRound::class, 'server_id', 'server_id')
            ->whereNull('ended_at')
            ->latestOfMany();
    }
}
