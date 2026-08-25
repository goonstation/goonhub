<?php

namespace App\Models;

use App\Models\Traits\IndexFilterScope;
use EloquentFilter\Filterable;
use Spatie\Permission\Models\Role as ModelsRole;

/**
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Role filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Role indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc', int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc', int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Role newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Role permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Role query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Role whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Role whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Role whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Role whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Role whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Role withoutPermission($permissions)
 *
 * @mixin \Eloquent
 */
class Role extends ModelsRole
{
    use Filterable;
    use IndexFilterScope;
}
