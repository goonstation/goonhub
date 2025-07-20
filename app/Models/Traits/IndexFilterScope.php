<?php

namespace App\Models\Traits;

use EloquentFilter\ModelFilter;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

trait IndexFilterScope
{
    private function applyIndexFilter(
        Builder $query,
        ModelFilter|string|null $filter = null,
        string $sortBy = 'id',
        bool $desc = true,
        int $perPage = 15
    ) {
        $request = request();

        // @phpstan-ignore method.notFound
        $query->filter($request->input('filters', []), $filter);

        $desc = $request->input('descending', $desc);
        $query->orderBy(
            $request->input('sort_by', $sortBy),
            $desc === 'true' || $desc === '1' || $desc === true ? 'desc' : 'asc'
        );

        $maxPerPage = 100;
        $perPage = (int) $request->input('per_page', $perPage);
        if ($perPage > $maxPerPage && ! $request->user()?->isAdmin()) {
            $perPage = $maxPerPage;
        }

        return $query;
    }

    /**
     * Paginate/filter/sort a model
     *
     * @return LengthAwarePaginator
     *
     * @throws BindingResolutionException
     */
    public function scopeIndexFilterPaginate(
        Builder $query,
        ModelFilter|string|null $filter = null,
        string $sortBy = 'id',
        bool $desc = true,
        int $perPage = 15,
        bool $simple = false
    ) {
        $query = $this->applyIndexFilter($query, $filter, $sortBy, $desc, $perPage);

        /** @var LengthAwarePaginator */
        $paginator = $simple ?
            // @phpstan-ignore method.notFound
            $query->simplePaginateFilter($perPage) :
            // @phpstan-ignore method.notFound
            $query->paginateFilter($perPage);

        return $paginator;
    }

    /**
     * Filter/sort a model
     *
     * @return Builder
     *
     * @throws BindingResolutionException
     */
    public function scopeIndexFilter(
        Builder $query,
        ModelFilter|string|null $filter = null,
        string $sortBy = 'id',
        bool $desc = true,
        int $limit = 15
    ) {
        return $this->applyIndexFilter($query, $filter, $sortBy, $desc, $limit);
    }
}
