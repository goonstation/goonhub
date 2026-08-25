<?php

namespace App\Providers;

use App\Http\Middleware\CanAccessAdminRoutes;
use App\Http\Middleware\ValidateFromGameServer;
use App\Http\Middleware\ValidateTargetGameAdmin;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        if (config('forcehttps.force_https')) {
            resolve(\Illuminate\Routing\UrlGenerator::class)->forceScheme('https');
        }

        $this->routes(function () {
            Route::middleware(['sentry:web-api', 'api'])
                ->name('web-api.')
                ->group(base_path('routes/web-api.php'));

            Route::middleware(['sentry:api-open', 'api'])
                ->domain(config('app.api_url'))
                ->name('api-open.')
                ->group(base_path('routes/api-open.php'));

            Route::middleware([
                'sentry:api',
                'auth:sanctum',
                'api',
                ValidateFromGameServer::class,
                ValidateTargetGameAdmin::class,
            ])
                ->domain(config('app.api_url'))
                ->name('api.')
                ->group(base_path('routes/api.php'));

            if (config('goonhub.include_frontend')) {
                Route::middleware(['sentry:web', 'web'])
                    ->name('web.')
                    ->group(base_path('routes/web.php'));

                Route::middleware([
                    'sentry:web',
                    'web',
                    'auth:sanctum',
                    config('jetstream.auth_session'),
                    'nometa',
                ])
                    ->name('web.user.')
                    ->group(base_path('routes/user.php'));

                Route::middleware([
                    'sentry:web',
                    'web',
                    'auth:sanctum',
                    config('jetstream.auth_session'),
                    'nometa',
                    CanAccessAdminRoutes::class,
                ])
                    ->prefix('/admin')
                    ->name('admin.')
                    ->group(base_path('routes/admin.php'));
            }
        });
    }
}
