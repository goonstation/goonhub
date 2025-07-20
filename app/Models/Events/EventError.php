<?php

namespace App\Models\Events;

use App\Models\GameRound;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $round_id
 * @property string|null $name
 * @property string|null $file
 * @property int|null $line
 * @property string|null $desc
 * @property string|null $user
 * @property string|null $user_ckey
 * @property bool $invalid
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\GameRound $gameRound
 * @property-read mixed $signature
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventError filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventError indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventError newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventError newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventError query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventError whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventError whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventError whereDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventError whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventError whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventError whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventError whereInvalid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventError whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventError whereLine($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventError whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventError whereRoundId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventError whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventError whereUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventError whereUserCkey($value)
 *
 * @mixin \Eloquent
 */
class EventError extends BaseEventModel
{
    use HasFactory;

    protected $table = 'events_errors';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'round_ids' => 'array',
            'server_ids' => 'array',
            'round_error_counts' => 'array',
            'overview_count' => 'int',
            'overview_round_count' => 'int',
        ];
    }

    protected function signature(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => md5(
                $attributes['name'].
                $attributes['file'].
                $attributes['line']
            ),
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function gameRound()
    {
        return $this->belongsTo(GameRound::class, 'round_id');
    }
}
