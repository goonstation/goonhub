<?php

namespace App\Models;

use App\Models\Traits\IndexFilterScope;
use EloquentFilter\Filterable;
use Spatie\Permission\Models\Permission as ModelsPermission;

/**
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Permission filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Permission indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc', int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc', int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Permission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Permission newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Permission permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Permission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Permission role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Permission whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Permission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Permission whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Permission whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Permission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Permission whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Permission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Permission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Permission withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Permission withoutRole($roles, $guard = null)
 *
 * @mixin \Eloquent
 */
class Permission extends ModelsPermission
{
    use Filterable;
    use IndexFilterScope;
}
