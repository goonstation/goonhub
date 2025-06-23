<?php

namespace App\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $player_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Player $player
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GameServer> $servers
 * @property-read int|null $servers_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerWhitelist filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerWhitelist newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerWhitelist newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerWhitelist paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerWhitelist query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerWhitelist simplePaginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerWhitelist whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerWhitelist whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerWhitelist whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerWhitelist whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerWhitelist whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerWhitelist wherePlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerWhitelist whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PlayerWhitelist extends Model
{
    use Filterable;

    protected $table = 'player_whitelist';

    protected $fillable = [
        'player_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function player()
    {
        return $this->belongsTo(Player::class, 'player_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function servers()
    {
        return $this->belongsToMany(GameServer::class, 'player_whitelist_servers', 'player_whitelist_id', 'server_id')->withTimestamps();
    }
}
