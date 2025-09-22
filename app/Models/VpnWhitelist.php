<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $game_admin_id
 * @property string $ckey
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\PlayerAdmin $gameAdmin
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\VpnWhitelist filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\VpnWhitelist indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc', int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc', int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\VpnWhitelist newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\VpnWhitelist newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\VpnWhitelist query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\VpnWhitelist whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\VpnWhitelist whereCkey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\VpnWhitelist whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\VpnWhitelist whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\VpnWhitelist whereGameAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\VpnWhitelist whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\VpnWhitelist whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\VpnWhitelist whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class VpnWhitelist extends BaseModel
{
    use HasFactory;

    protected $table = 'vpn_whitelist';

    protected $fillable = [
        'ckey',
        'game_admin_id',
    ];

    public function gameAdmin(): BelongsTo
    {
        return $this->belongsTo(PlayerAdmin::class);
    }
}
