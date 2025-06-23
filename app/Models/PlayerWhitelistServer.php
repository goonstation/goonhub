<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $player_whitelist_id
 * @property int $server_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PlayerWhitelist $playerWhitelist
 * @property-read \App\Models\GameServer $server
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerWhitelistServer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerWhitelistServer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerWhitelistServer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerWhitelistServer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerWhitelistServer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerWhitelistServer wherePlayerWhitelistId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerWhitelistServer whereServerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerWhitelistServer whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PlayerWhitelistServer extends Model
{
    protected $fillable = [
        'server_id',
    ];

    public function playerWhitelist()
    {
        return $this->belongsTo(PlayerWhitelist::class, 'player_whitelist_id');
    }

    public function server()
    {
        return $this->belongsTo(GameServer::class, 'server_id');
    }
}
