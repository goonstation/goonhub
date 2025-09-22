<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int|null $round_id
 * @property string $ip
 * @property string $service
 * @property string|null $response
 * @property string|null $error
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\VpnCheck filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\VpnCheck indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc', int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc', int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\VpnCheck newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\VpnCheck newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\VpnCheck query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\VpnCheck whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\VpnCheck whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\VpnCheck whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\VpnCheck whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\VpnCheck whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\VpnCheck whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\VpnCheck whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\VpnCheck whereResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\VpnCheck whereRoundId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\VpnCheck whereService($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\VpnCheck whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class VpnCheck extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'round_id',
        'ip',
        'service',
        'error',
        'response',
    ];
}
