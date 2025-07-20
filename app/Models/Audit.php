<?php

namespace App\Models;

use App\Models\Traits\IndexFilterScope;
use EloquentFilter\Filterable;
use OwenIt\Auditing\Models\Audit as ModelsAudit;
use Str;

/**
 * @property int $id
 * @property string|null $user_type
 * @property int|null $user_id
 * @property string $event
 * @property string $auditable_type
 * @property int $auditable_id
 * @property array<array-key, mixed>|null $old_values
 * @property array<array-key, mixed>|null $new_values
 * @property string|null $url
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $tags
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $auditable
 * @property-read mixed $auditable_clean_type
 * @property-read mixed $auditable_label
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Audit filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Audit indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Audit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Audit newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Audit query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Audit whereAuditableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Audit whereAuditableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Audit whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Audit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Audit whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Audit whereEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Audit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Audit whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Audit whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Audit whereNewValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Audit whereOldValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Audit whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Audit whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Audit whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Audit whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Audit whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Audit whereUserType($value)
 *
 * @mixin \Eloquent
 */
class Audit extends ModelsAudit
{
    use Filterable, IndexFilterScope;

    public function getAuditableCleanTypeAttribute()
    {
        return Str::replace('App\\Models\\', '', $this->auditable_type);
    }

    public function getAuditableLabelAttribute()
    {
        $type = Str::replace('App\\Models\\', '', $this->auditable_type);

        return preg_replace('/(?<! )(?<!^)[A-Z]/', ' $0', $type);
    }
}
