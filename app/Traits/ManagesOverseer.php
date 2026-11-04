<?php

namespace App\Traits;

use App\Models\GameServer;
use App\Services\Overseer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Throwable;

trait ManagesOverseer
{
    private function getServerStatus(Request $request)
    {
        $request->validate([
            'server' => 'nullable|string|exists:game_servers,server_id',
        ]);

        $serverId = $request->input('server', null);
        $servers = collect();
        if ($serverId) {
            $servers->add(GameServer::firstWhere('server_id', $serverId));
        } else {
            $servers = GameServer::where('active', true)->orderBy('server_id')->get();
        }

        $cachedData = [];
        foreach ($servers as $server) {
            $cacheKey = "overseer-status-{$server->server_id}";
            $cacheItem = Cache::get($cacheKey);
            if ($cacheItem) {
                $cachedData[$server->server_id] = $cacheItem;
            }
        }

        if (count($servers) === count($cachedData)) {
            return $cachedData;
        }

        $status = [];
        $overseerGroups = $servers->groupBy('overseer');
        foreach ($overseerGroups as $overseerUrl => $group) {
            $res = app(Overseer::class)->status($overseerUrl ?: '', $serverId);
            $status = array_merge($status, $res);
        }

        foreach ($status as $resServerId => $singleStatus) {
            $cacheKey = "overseer-status-$resServerId";
            Cache::set($cacheKey, $singleStatus, 55);
        }

        ksort($status);

        return $status;
    }

    private function restartServer(Request $request)
    {
        $request->validate([
            'server' => 'required|string|exists:game_servers,server_id',
        ]);

        $server = GameServer::firstWhere('server_id', $request['server']);
        $lock = Cache::lock("overseer-restart-{$server->server_id}", 60);
        if (! $lock->get()) {
            throw new \Exception('Restarted recently');
        }

        try {
            $operation = app(Overseer::class)->restart($server->overseer ?? '', $server->server_id);
        } catch (Throwable $exception) {
            $lock->release();
            throw $exception;
        }
        Cache::forget("overseer-status-{$server->server_id}");

        return $operation;
    }

    private function getRestartOperation(Request $request, string $id): array
    {
        $data = $request->validate(['server' => 'required|string|exists:game_servers,server_id']);
        $server = GameServer::firstWhere('server_id', $data['server']);
        $operation = app(Overseer::class)->operation($server->overseer ?? '', $id);
        if (($operation['server'] ?? null) !== $server->server_id) {
            throw new RuntimeException('Overseer operation belongs to another server.');
        }
        if (in_array($operation['state'] ?? null, ['succeeded', 'failed'], true)) {
            Cache::forget("overseer-status-{$server->server_id}");
        }

        return $operation;
    }
}
