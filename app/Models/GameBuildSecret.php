<?php

namespace App\Models;

/**
 * @property int $id
 * @property string $key
 * @property string $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSecret filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSecret indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSecret newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSecret newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSecret query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSecret whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSecret whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSecret whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSecret whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSecret whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSecret whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSecret whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildSecret whereValue($value)
 *
 * @mixin \Eloquent
 */
class GameBuildSecret extends BaseModel
{
    //
}
