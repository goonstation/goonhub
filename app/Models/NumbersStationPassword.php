<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $password
 * @property string $numbers
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NumbersStationPassword filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NumbersStationPassword indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NumbersStationPassword newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NumbersStationPassword newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NumbersStationPassword query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NumbersStationPassword whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NumbersStationPassword whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NumbersStationPassword whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NumbersStationPassword whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NumbersStationPassword whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NumbersStationPassword whereNumbers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NumbersStationPassword wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NumbersStationPassword whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class NumbersStationPassword extends BaseModel
{
    use HasFactory;

    protected $table = 'numbers_station_password';

    public static $auditingDisabled = true;
}
