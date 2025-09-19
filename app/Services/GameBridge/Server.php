<?php

namespace App\Services\GameBridge;

use App\Models\GameServer;

class Server
{
    private GameBridgeService $gameBridge;

    private Target $target;

    private int $timeout;

    private bool $force = false;

    private int $cacheFor;

    public function __construct(GameBridgeService $gameBridge, string|GameServer $server)
    {
        $this->gameBridge = $gameBridge;
        $this->target = $gameBridge->resolveTarget($server);
        $this->timeout = $gameBridge->getTimeout();
        $this->cacheFor = $gameBridge->getDefaultCacheTime();
    }

    /**
     * Get the server ID being targeted
     */
    public function getServerId(): string
    {
        return $this->target->getServerId();
    }

    /**
     * Set timeout for requests
     */
    public function timeout(int $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Force bypass cache
     */
    public function force(bool $force = true): self
    {
        $this->force = $force;

        return $this;
    }

    /**
     * Set cache duration
     */
    public function cacheFor(int $seconds): self
    {
        $this->cacheFor = $seconds;

        return $this;
    }

    /**
     * Get server status
     */
    public function status(): GameBridgeResponse
    {
        return $this->send('status');
    }

    /**
     * Get player count
     */
    public function players(): GameBridgeResponse
    {
        return $this->send('players');
    }

    /**
     * Send raw message
     */
    public function send(string|array $message): GameBridgeResponse
    {
        $connection = new Connection($this->gameBridge);

        return $connection
            ->target($this->target)
            ->message($message)
            ->timeout($this->timeout)
            ->force($this->force)
            ->cacheFor($this->cacheFor)
            ->send();
    }

    /**
     * Send and forget
     */
    public function sendAndForget(string|array $message): void
    {
        $connection = new Connection($this->gameBridge);

        $connection
            ->target($this->target)
            ->message($message)
            ->timeout($this->timeout)
            ->force($this->force)
            ->cacheFor($this->cacheFor)
            ->sendAndForget();
    }
}
