<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int $voteable_id
 * @property string $voteable_type
 * @property string $ip
 * @property int $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $voteable
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Vote filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Vote indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc', int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc', int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Vote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Vote newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Vote query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Vote whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Vote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Vote whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Vote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Vote whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Vote whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Vote whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Vote whereValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Vote whereVoteableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Vote whereVoteableType($value)
 *
 * @mixin \Eloquent
 */
class Vote extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'ip',
        'value',
    ];

    public static $auditingDisabled = true;

    public function voteable(): MorphTo
    {
        return $this->morphTo();
    }
}
