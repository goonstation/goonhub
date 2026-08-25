<?php

namespace App\Console\Commands;

use App\Facades\GameBridge;
use App\Models\Events\EventLog;
use App\Traits\ManagesUsers;
use Illuminate\Console\Command;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Laravel\Octane\Facades\Octane;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Models\Permission;
use Str;

class DevTest extends Command
{
    use ManagesUsers;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gh:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // $guardName = 'sanctum';

        // $existingPermissions = Permission::where('guard_name', $guardName)->get()->pluck('name');

        // $permissions = collect(Route::getRoutes()->getRoutes())
        //     ->filter(function (RoutingRoute $route) {
        //         return str_starts_with($route->getControllerClass(), 'App\Http\Controllers\Api');
        //     })
        //     ->map(function (RoutingRoute $route) {
        //         return collect($route->gatherMiddleware())
        //             ->filter(function ($middleware) {
        //                 return str_starts_with($middleware, 'ability:') || str_starts_with($middleware, 'abilities:');
        //             })
        //             ->map(function ($middleware) use ($route) {
        //                 $abilities = explode(',', str_replace('ability:', '', str_replace('abilities:', '', $middleware)));

        //                 $groups = [];
        //                 foreach ($abilities as $ability) {
        //                     $groups[] = [
        //                         'name' => $ability,
        //                         'group' => implode(' ', preg_split(
        //                             '/(?=[A-Z])/',
        //                             str_replace('Controller', '', class_basename($route->getControllerClass())),
        //                             -1,
        //                             PREG_SPLIT_NO_EMPTY
        //                         )),
        //                     ];
        //                 }

        //                 return $groups;
        //             })
        //             ->flatten(1);
        //     })
        //     ->filter(fn ($abilities) => $abilities->isNotEmpty())
        //     ->flatten(1)
        //     ->unique('name')
        //     ->filter(function ($ability) use ($existingPermissions) {
        //         return ! $existingPermissions->contains($ability['name']);
        //     })
        //     ->values()
        //     ->map(function ($ability) use ($guardName) {
        //         return [
        //             'name' => $ability['name'],
        //             // 'group' => $ability['group'],
        //             'guard_name' => $guardName,
        //             'created_at' => now()->toDateTimeString(),
        //             'updated_at' => now()->toDateTimeString(),
        //         ];
        //     });

        // $routes = collect(Route::getRoutes()->getRoutesByName())
        //     ->filter(fn ($route) => Str::is(config('ziggy.groups.web'), $route->getName()))
        //     ->reject(fn ($route) => str_contains($route->getName(), 'generated::'))
        //     ->map(function ($route) {
        //         $permissionPrefixes = [
        //             'can:',
        //             'permission:',
        //             PermissionMiddleware::class.':',
        //         ];

        //         $rolePrefixes = [
        //             'role:',
        //             RoleMiddleware::class.':',
        //         ];

        //         $middlewares = collect($route->gatherMiddleware());

        //         return [
        //             // 'name' => $route->getName(),
        //             'permissions' => $middlewares
        //                 ->filter(fn ($middleware) => Str::startsWith($middleware, $permissionPrefixes))
        //                 ->map(fn ($middleware) => Str::replace($permissionPrefixes, '', $middleware))
        //                 ->unique()->values()->toArray(),
        //             'roles' => $middlewares
        //                 ->filter(fn ($middleware) => Str::startsWith($middleware, $rolePrefixes))
        //                 ->map(fn ($middleware) => Str::replace($rolePrefixes, '', $middleware))
        //                 ->unique()->values()->toArray(),
        //         ];
        //     });

        // dump($routes->toArray());

        // $tasks = Octane::concurrently([
        //     '1' => function () {
        //         return GameBridge::noRetry()->server('dev')->status()->getData()['station_name'];
        //     },
        //     '2' => function () {
        //         return GameBridge::noRetry()->server('dev')->status()->getData()['station_name'];
        //     },
        //     '3' => function () {
        //         return GameBridge::noRetry()->server('dev')->status()->getData()['station_name'];
        //     },
        //     '4' => function () {
        //         return GameBridge::noRetry()->server('dev')->status()->getData()['station_name'];
        //     },
        // ], 30000);

        // $status = GameBridge::noRetry()->server('dev')->status();
        // dump($status->getData());

        $logs = EventLog::search('chem dispenser')
            ->orderByRelevance('chem dispenser')
            ->limit(3)
            ->get();
        dump($logs->toArray());

        return Command::SUCCESS;
    }
}
