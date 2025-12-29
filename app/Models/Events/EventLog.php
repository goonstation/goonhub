<?php

namespace App\Models\Events;

use App\Models\GameRound;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $round_id
 * @property string|null $type
 * @property string|null $source
 * @property string|null $message
 * @property string|null $search_vector
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\GameRound $gameRound
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventLog filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventLog indexFilter(\EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc')
 * @method static \Illuminate\Pagination\LengthAwarePaginator indexFilterPaginate(\Illuminate\Database\Eloquent\Builder $query, \EloquentFilter\ModelFilter|string|null $filter = null, array $default = [], string $sortBy = 'id', string $order = 'desc', int $perPage = 15, bool $simple = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventLog newQuery()
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventLog query()
 * @method static \Illuminate\Pagination\LengthAwarePaginator simplePaginateFilter($query, $perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventLog whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventLog whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventLog whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventLog whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventLog whereRoundId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventLog whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventLog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventLog search(string $term)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Events\EventLog searchPhrase(string $phrase)
 *
 * @mixin \Eloquent
 */
class EventLog extends BaseEventModel
{
    use HasFactory;

    protected $table = 'events_logs';

    /**
     * The attributes that should be hidden for serialization.
     * Hide the search_vector column from JSON responses.
     *
     * @var list<string>
     */
    protected $hidden = ['search_vector'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<GameRound, $this>
     */
    public function gameRound()
    {
        return $this->belongsTo(GameRound::class, 'round_id');
    }

    /**
     * Scope for PostgreSQL full-text search using the GIN-indexed tsvector column.
     *
     * Uses plainto_tsquery for simple word-based searches.
     * Words are stemmed and stop words are removed automatically.
     *
     * @example EventLog::search('player explosion')->get()
     * @example EventLog::where('round_id', 123)->search('admin ban')->get()
     *
     * @param  Builder<EventLog>  $query
     * @return Builder<EventLog>
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->whereRaw(
            "search_vector @@ plainto_tsquery('english', ?)",
            [$term]
        );
    }

    /**
     * Scope for exact phrase search using PostgreSQL full-text search.
     *
     * Uses phraseto_tsquery for phrase searches where word order matters.
     *
     * @example EventLog::searchPhrase('killed by explosion')->get()
     *
     * @param  Builder<EventLog>  $query
     * @return Builder<EventLog>
     */
    public function scopeSearchPhrase(Builder $query, string $phrase): Builder
    {
        return $query->whereRaw(
            "search_vector @@ phraseto_tsquery('english', ?)",
            [$phrase]
        );
    }

    /**
     * Scope to order results by search relevance.
     *
     * @param  Builder<EventLog>  $query
     * @return Builder<EventLog>
     */
    public function scopeOrderByRelevance(Builder $query, string $term): Builder
    {
        return $query->orderByRaw(
            "ts_rank(search_vector, plainto_tsquery('english', ?)) DESC",
            [$term]
        );
    }
}
