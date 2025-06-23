<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $player_bypass_cap_id
 * @property int $server_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PlayerBypassCap $playerBypassCap
 * @property-read \App\Models\GameServer $server
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerBypassCapServer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerBypassCapServer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerBypassCapServer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerBypassCapServer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerBypassCapServer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerBypassCapServer wherePlayerBypassCapId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerBypassCapServer whereServerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerBypassCapServer whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PlayerBypassCapServer extends Model
{
    protected $fillable = [
        'server_id',
    ];

    public function playerBypassCap()
    {
        return $this->belongsTo(PlayerBypassCap::class, 'player_bypass_cap_id');
    }

    public function server()
    {
        return $this->belongsTo(GameServer::class, 'server_id');
    }
}
