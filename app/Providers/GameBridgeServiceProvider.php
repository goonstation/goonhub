<?php

namespace App\Providers;

use App\Services\GameBridge\GameBridgeService;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class GameBridgeServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the new service
        $this->app->singleton(GameBridgeService::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            GameBridgeService::class,
        ];
    }
}
