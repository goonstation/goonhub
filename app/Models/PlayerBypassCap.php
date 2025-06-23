<?php

namespace App\Models;

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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerBypassCap newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerBypassCap newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerBypassCap query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerBypassCap whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerBypassCap whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerBypassCap wherePlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerBypassCap whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PlayerBypassCap extends Model
{
    protected $table = 'player_bypass_cap';

    protected $fillable = [
        'player_id',
    ];

    public function player()
    {
        return $this->belongsTo(Player::class, 'player_id');
    }

    public function servers()
    {
        return $this->belongsToMany(GameServer::class, 'player_bypass_cap_servers', 'player_bypass_cap_id', 'server_id')->withTimestamps();
    }
}
