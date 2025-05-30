<?php

namespace App\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $server_id
 * @property int|null $online
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\GameServer $server
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayersOnline filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayersOnline newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayersOnline newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayersOnline paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayersOnline query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayersOnline simplePaginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayersOnline whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayersOnline whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayersOnline whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayersOnline whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayersOnline whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayersOnline whereOnline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayersOnline whereServerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayersOnline whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PlayersOnline extends BaseModel
{
    use Filterable, HasFactory;

    protected $table = 'players_online';

    public static $auditingDisabled = true;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function server()
    {
        return $this->belongsTo(GameServer::class, 'server_id');
    }
}
