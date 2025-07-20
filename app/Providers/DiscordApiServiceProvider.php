<?php

namespace App\Providers;

use App\Services\Discord\Api;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class DiscordApiServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(Api::class, function ($app) {
            return new Api;
        });

        $this->app->alias(Api::class, 'discord-api');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            Api::class,
            'discord-api',
        ];
    }
}
