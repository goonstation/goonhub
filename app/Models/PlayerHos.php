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
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerHos filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerHos newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerHos newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerHos paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerHos query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerHos simplePaginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerHos whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerHos whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerHos whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerHos whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerHos whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerHos wherePlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerHos whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PlayerHos extends Model
{
    use Filterable;

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
}
