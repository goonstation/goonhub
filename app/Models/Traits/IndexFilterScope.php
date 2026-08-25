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
        string $order = 'desc'
    ) {
        $request = request();

        $filters = $request->input('filters', []);
        $filters = array_merge($default, $filters);

        // Legacy support for previous index filtering implementation
        // TODO: remove when we've fully migrated to the new implementation
        if ($request->filled('descending')) {
            $descInput = $request->input('descending');
            $order = $descInput === 'true' || $descInput === '1' || $descInput === true ? 'desc' : 'asc';
        }
        if ($request->filled('sort_by')) {
            $sortBy = $request->input('sort_by');
        }

        $filters['order'] = $filters['order'] ?? $order;
        $filters['sort'] = $filters['sort'] ?? $sortBy;

        // @phpstan-ignore method.notFound
        $query->filter($filters, $filter);

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
        $request = request();
        $query = $this->applyIndexFilter($query, $filter, $default, $sortBy, $order);

        $maxPerPage = 100;
        $perPage = (int) $request->input('per_page', $perPage);
        if ($perPage > $maxPerPage && ! $request->user()?->isAdmin()) {
            $perPage = $maxPerPage;
        }

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
        string $order = 'desc'
    ) {
        return $this->applyIndexFilter($query, $filter, $default, $sortBy, $order);
    }
}
