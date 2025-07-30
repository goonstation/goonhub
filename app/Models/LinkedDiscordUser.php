<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $discord_id
 * @property string|null $name
 * @property string|null $email
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LinkedDiscordUser filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LinkedDiscordUser indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, string $sortBy = 'id', bool $desc = true, int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LinkedDiscordUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LinkedDiscordUser newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LinkedDiscordUser query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LinkedDiscordUser whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LinkedDiscordUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LinkedDiscordUser whereDiscordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LinkedDiscordUser whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LinkedDiscordUser whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LinkedDiscordUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LinkedDiscordUser whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LinkedDiscordUser whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LinkedDiscordUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LinkedDiscordUser whereUserId($value)
 *
 * @mixin \Eloquent
 */
class LinkedDiscordUser extends BaseModel
{
    protected $fillable = [
        'user_id',
        'discord_id',
        'name',
        'email',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
