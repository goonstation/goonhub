<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $pr_id
 * @property int $setting_id
 * @property int|null $added_by
 * @property int|null $updated_by
 * @property string|null $commit
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PlayerAdmin|null $addedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\GameBuildSetting $buildSettings
 * @property-read \App\Models\PlayerAdmin|null $updatedBy
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildTestMerge filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildTestMerge indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildTestMerge newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildTestMerge newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildTestMerge query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildTestMerge whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildTestMerge whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildTestMerge whereCommit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildTestMerge whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildTestMerge whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildTestMerge whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildTestMerge whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildTestMerge wherePrId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildTestMerge whereSettingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildTestMerge whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GameBuildTestMerge whereUpdatedBy($value)
 *
 * @mixin \Eloquent
 */
class GameBuildTestMerge extends BaseModel
{
    use HasFactory;

    protected $table = 'game_build_test_merges';

    public function buildSettings(): BelongsTo
    {
        return $this->belongsTo(GameBuildSetting::class, 'setting_id');
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(PlayerAdmin::class, 'added_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(PlayerAdmin::class, 'updated_by');
    }
}
