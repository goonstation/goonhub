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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerMentor filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerMentor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerMentor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerMentor paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerMentor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerMentor simplePaginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerMentor whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerMentor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerMentor whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerMentor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerMentor whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerMentor wherePlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayerMentor whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PlayerMentor extends Model
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
