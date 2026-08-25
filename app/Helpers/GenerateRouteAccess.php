<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Str;

class GenerateRouteAccess
{
    public static function getRoutePermissions(Route|string $route): array
    {
        $route = $route instanceof Route ? $route : Route::getRoutes()->getByName($route);
        $middlewares = collect($route->gatherMiddleware());
        $permissionPrefixes = ['can:', 'permission:', 'ability:', 'abilities:', PermissionMiddleware::class.':'];
        $rolePrefixes = ['role:', RoleMiddleware::class.':'];

        $permissions = $middlewares
            ->filter(fn ($middleware) => Str::startsWith($middleware, $permissionPrefixes))
            ->flatMap(fn ($middleware) => explode(',', Str::replace($permissionPrefixes, '', $middleware)))
            ->unique()->values()->toArray();
        $roles = $middlewares
            ->filter(fn ($middleware) => Str::startsWith($middleware, $rolePrefixes))
            ->map(fn ($middleware) => Str::replace($rolePrefixes, '', $middleware))
            ->unique()->values()->toArray();

        $data = [];
        if (! empty($permissions)) {
            $data['permissions'] = $permissions;
        }
        if (! empty($roles)) {
            $data['roles'] = $roles;
        }

        return $data;

    }

    public static function generate(): string
    {
        $routes = collect(Route::getRoutes()->getRoutesByName())
            ->filter(fn ($route) => Str::is(config('ziggy.groups.web'), $route->getName()))
            ->reject(fn ($route) => str_contains($route->getName(), 'generated::'))
            ->map(function ($route) {
                $permissionPrefixes = [
                    'can:',
                    'permission:',
                    PermissionMiddleware::class.':',
                ];

                $rolePrefixes = [
                    'role:',
                    RoleMiddleware::class.':',
                ];

                $middlewares = collect($route->gatherMiddleware());

                return [
                    'permissions' => $middlewares
                        ->filter(fn ($middleware) => Str::startsWith($middleware, $permissionPrefixes))
                        ->map(fn ($middleware) => Str::replace($permissionPrefixes, '', $middleware))
                        ->unique()->values()->toArray(),
                    'roles' => $middlewares
                        ->filter(fn ($middleware) => Str::startsWith($middleware, $rolePrefixes))
                        ->map(fn ($middleware) => Str::replace($rolePrefixes, '', $middleware))
                        ->unique()->values()->toArray(),
                ];
            })
            ->reject(fn ($route) => empty($route['permissions']) && empty($route['roles']));

        return <<<HTML
        <script type="text/javascript">const RouteAccess={$routes->toJson()};</script>
        HTML;
    }
}
