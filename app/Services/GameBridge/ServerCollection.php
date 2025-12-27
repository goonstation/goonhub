<?php

namespace App\Services\GameBridge;

use Illuminate\Database\Eloquent\Collection as ModelCollection;
use Illuminate\Support\Collection;

class ServerCollection
{
    private GameBridgeService $gameBridge;

    private Collection $targets;

    private int $timeout;

    private bool $force = false;

    private int $cacheFor;

    private ?string $priority = null;

    public function __construct(GameBridgeService $gameBridge, array|ModelCollection $servers)
    {
        $this->gameBridge = $gameBridge;
        $this->targets = $gameBridge->resolveTargets($servers);
        $this->timeout = $gameBridge->getTimeout();
        $this->cacheFor = $gameBridge->getDefaultCacheTime();
    }

    /**
     * Get the server IDs being targeted
     */
    public function getServerIds(): array
    {
        return $this->targets->map(function ($target) {
            return $target->getServerId();
        })->toArray();
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
     * Set priority
     */
    public function priority(string $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get status from all servers
     */
    public function status(): Collection
    {
        if (! $this->priority) {
            $this->priority = 'low';
        }

        return $this->send('status');
    }

    /**
     * Get player count from all servers
     */
    public function players(): Collection
    {
        if (! $this->priority) {
            $this->priority = 'low';
        }

        return $this->send('players');
    }

    /**
     * Send message to all servers
     */
    public function send(string|array $message): Collection
    {
        $connection = new Connection($this->gameBridge);

        $result = $connection
            ->targets($this->targets)
            ->message($message)
            ->timeout($this->timeout)
            ->force($this->force)
            ->cacheFor($this->cacheFor)
            ->priority($this->priority ?? 'medium')
            ->send();

        // Always return a Collection for batch operations
        return $result instanceof Collection ? $result : collect([$result]);
    }

    /**
     * Send and forget to all servers
     */
    public function sendAndForget(string|array $message): void
    {
        $connection = new Connection($this->gameBridge);

        $connection
            ->targets($this->targets)
            ->message($message)
            ->timeout($this->timeout)
            ->force($this->force)
            ->cacheFor($this->cacheFor)
            ->priority($this->priority ?? 'medium')
            ->sendAndForget();
    }

    /**
     * Get count of servers
     */
    public function count(): int
    {
        return $this->targets->count();
    }

    /**
     * Check if collection is empty
     */
    public function isEmpty(): bool
    {
        return $this->targets->isEmpty();
    }

    /**
     * Get the first server as a Server instance
     */
    public function first(): ?Server
    {
        $firstTarget = $this->targets->first();

        if (! $firstTarget) {
            return null;
        }

        return $this->gameBridge->server($firstTarget->getServerId());
    }

    /**
     * Map over each server and return a new collection
     */
    public function map(callable $callback): Collection
    {
        return $this->targets->map($callback);
    }

    /**
     * Get each server as a Server instance
     */
    public function each(callable $callback): self
    {
        $this->targets->each(function ($target) use ($callback) {
            $server = $this->gameBridge->server($target->getServerId());
            $callback($server, $target);
        });

        return $this;
    }
}
