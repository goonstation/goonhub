<?php

namespace App\Facades;

use App\Services\GameBridge\GameBridgeService;
use Illuminate\Support\Facades\Facade;

/**
 * GameBridge Service Facade
 *
 * @method static \App\Services\GameBridge\Server server(string|\App\Models\GameServer $server)
 * @method static \App\Services\GameBridge\ServerCollection servers(array|\Illuminate\Database\Eloquent\Collection $servers)
 * @method static \App\Services\GameBridge\GameBridgeResponse send(string|array $targets, string|array $message)
 * @method static void sendAndForget(string|array $targets, string|array $message)
 * @method static \App\Services\GameBridge\Target resolveTarget(string|\App\Models\GameServer $target)
 * @method static \Illuminate\Support\Collection resolveTargets(array|\Illuminate\Database\Eloquent\Collection $targets)
 * @method static \App\Services\GameBridge\Connection connection()
 * @method static \App\Services\GameBridge\GameBridgeResponse executeConnection(\App\Services\GameBridge\Target $target, object $options)
 * @method static int getTimeout()
 * @method static int getRetryAttempts()
 * @method static int getRetryDelay()
 * @method static int getDefaultCacheTime()
 *
 * @see GameBridgeService
 */
class GameBridge extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return GameBridgeService::class;
    }
}
