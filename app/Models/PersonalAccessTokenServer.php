<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $personal_access_token_id
 * @property int|null $server_id
 * @property int|null $server_group_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\PersonalAccessToken $personalAccessToken
 * @property-read \App\Models\GameServer|null $server
 * @property-read \App\Models\GameServerGroup|null $serverGroup
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessTokenServer filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessTokenServer indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc', int $limit = 15)
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc', int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessTokenServer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessTokenServer newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessTokenServer query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessTokenServer whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessTokenServer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessTokenServer whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessTokenServer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessTokenServer whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessTokenServer wherePersonalAccessTokenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessTokenServer whereServerGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessTokenServer whereServerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessTokenServer whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PersonalAccessTokenServer extends BaseModel
{
    protected $fillable = [
        'personal_access_token_id',
        'server_id',
        'server_group_id',
    ];

    public function personalAccessToken(): BelongsTo
    {
        return $this->belongsTo(PersonalAccessToken::class, 'personal_access_token_id');
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(GameServer::class, 'server_id');
    }

    public function serverGroup(): BelongsTo
    {
        return $this->belongsTo(GameServerGroup::class, 'server_group_id');
    }
}
