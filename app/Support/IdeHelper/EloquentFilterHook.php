<?php

namespace App\Support\IdeHelper;

use Barryvdh\LaravelIdeHelper\Console\ModelsCommand;
use Barryvdh\LaravelIdeHelper\Contracts\ModelHookInterface;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentFilterHook implements ModelHookInterface
{
    public function run(ModelsCommand $command, Model $model): void
    {
        if (! in_array(Filterable::class, class_uses_recursive($model::class))) {
            return;
        }

        $command->unsetMethod('paginateFilter');
        $method = new \ReflectionMethod(Filterable::class, 'scopePaginateFilter');
        $args = $command->getParameters($method);
        $command->setMethod('paginateFilter', '\\'.LengthAwarePaginator::class, $args);

        $command->unsetMethod('simplePaginateFilter');
        $method = new \ReflectionMethod(Filterable::class, 'scopeSimplePaginateFilter');
        $args = $command->getParameters($method);
        $command->setMethod('simplePaginateFilter', '\\'.LengthAwarePaginator::class, $args);

        $command->unsetMethod('indexFilterPaginate');
        $method = new \ReflectionMethod($model, 'scopeIndexFilterPaginate');
        $args = $command->getParameters($method);
        $command->setMethod('indexFilterPaginate', '\\'.LengthAwarePaginator::class, $args);
    }
}
