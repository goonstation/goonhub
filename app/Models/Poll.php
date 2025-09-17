<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @property int $id
 * @property int|null $game_admin_id
 * @property string $question
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property bool $multiple_choice
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $servers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PollAnswer> $answers
 * @property-read int|null $answers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\PlayerAdmin|null $gameAdmin
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PollOption> $options
 * @property-read int|null $options_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Poll filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Poll indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Poll newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Poll newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Poll query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Poll whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Poll whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Poll whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Poll whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Poll whereGameAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Poll whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Poll whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Poll whereMultipleChoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Poll whereQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Poll whereServers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Poll whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Poll extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function gameAdmin(): BelongsTo
    {
        return $this->belongsTo(PlayerAdmin::class, 'game_admin_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class, 'poll_id');
    }

    public function answers(): HasManyThrough
    {
        return $this->hasManyThrough(PollAnswer::class, PollOption::class);
    }
}
