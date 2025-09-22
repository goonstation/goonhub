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
        array $default = [],
        string $sortBy = 'id',
        string $order = 'desc',
        int $perPage = 15
    ) {
        $request = request();

        $filters = $request->input('filters', []);
        $filters = array_merge($default, $filters);
        $filters['order'] = $filters['order'] ?? $order;
        $filters['sort'] = $filters['sort'] ?? $sortBy;

        // @phpstan-ignore method.notFound
        $query->filter($filters, $filter);

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
        array $default = [],
        string $sortBy = 'id',
        string $order = 'desc',
        int $perPage = 15,
        bool $simple = false
    ) {
        $query = $this->applyIndexFilter($query, $filter, $default, $sortBy, $order, $perPage);

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
        array $default = [],
        string $sortBy = 'id',
        string $order = 'desc',
        int $limit = 15
    ) {
        return $this->applyIndexFilter($query, $filter, $default, $sortBy, $order, $limit);
    }
}
