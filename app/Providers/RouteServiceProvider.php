<?php

namespace App\Providers;

use App\Http\Middleware\CanAccessAdminRoutes;
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
                ->group(base_path('routes/web-api.php'));

            Route::middleware(['sentry:api-open', 'api'])
                ->domain(config('app.api_url'))
                ->group(base_path('routes/api-open.php'));

            Route::middleware([
                'sentry:api',
                'auth:api',
                'api',
                ValidateTargetGameAdmin::class,
            ])
                ->domain(config('app.api_url'))
                ->group(base_path('routes/api.php'));

            if (config('goonhub.include_frontend')) {
                Route::middleware(['sentry:web', 'web'])
                    ->group(base_path('routes/web.php'));

                Route::middleware([
                    'sentry:web',
                    'web',
                    'auth:sanctum',
                    config('jetstream.auth_session'),
                    'nometa',
                ])
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
                    ->group(base_path('routes/admin.php'));
            }
        });
    }
}
