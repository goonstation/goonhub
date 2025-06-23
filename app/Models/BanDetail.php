<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $ban_id
 * @property string|null $ckey
 * @property string|null $comp_id
 * @property string|null $ip
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $player_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\Ban $ban
 * @property-read \App\Models\Player|null $player
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BanDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BanDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BanDetail onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BanDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BanDetail whereBanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BanDetail whereCkey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BanDetail whereCompId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BanDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BanDetail whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BanDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BanDetail whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BanDetail wherePlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BanDetail whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BanDetail withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BanDetail withoutTrashed()
 *
 * @mixin \Eloquent
 */
class BanDetail extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ckey',
        'comp_id',
        'ip',
        'player_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ban()
    {
        return $this->belongsTo(Ban::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function player()
    {
        return $this->belongsTo(Player::class);
    }
}
