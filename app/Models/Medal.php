<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property bool $hidden
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $uuid
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PlayerMedal> $earned
 * @property-read int|null $earned_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Medal filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Medal indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc', int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc', int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Medal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Medal newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Medal query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Medal whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Medal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Medal whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Medal whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Medal whereHidden($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Medal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Medal whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Medal whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Medal whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Medal whereUuid($value)
 *
 * @mixin \Eloquent
 */
class Medal extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'hidden',
    ];

    protected $hidden = [
        'id',
    ];

    // public function players()
    // {
    //     return $this->hasManyThrough(Player::class, PlayerMedal::class);
    // }

    public function earned(): HasMany
    {
        return $this->hasMany(PlayerMedal::class);
    }
}
