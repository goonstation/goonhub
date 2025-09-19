<?php

namespace App\Services\GameBridge;

use App\Models\GameServer;
use Illuminate\Database\Eloquent\Collection as ModelCollection;
use Illuminate\Support\Collection;
use RuntimeException;

class Connection
{
    private GameBridgeService $gameBridge;

    private $targets;

    private int $timeout;

    private ?string $message = null;

    private bool $force = false;

    private int $cacheFor;

    public function __construct(GameBridgeService $gameBridge)
    {
        $this->gameBridge = $gameBridge;
        $this->targets = collect();
        $this->timeout = $gameBridge->getTimeout();
        $this->cacheFor = $gameBridge->getDefaultCacheTime();
    }

    /**
     * Set a single target for this connection
     */
    public function target(string|GameServer|Target $target): self
    {
        if ($target instanceof Target) {
            $this->targets = $target;
        } else {
            $this->targets = $this->gameBridge->resolveTarget($target);
        }

        return $this;
    }

    /**
     * Set multiple targets for this connection
     */
    public function targets(array|ModelCollection|Collection $targets): self
    {
        if ($targets instanceof Collection && ! ($targets instanceof ModelCollection)) {
            // Already resolved Collection of target objects
            $this->targets = $targets;
        } else {
            $this->targets = $this->gameBridge->resolveTargets($targets);
        }

        return $this;
    }

    /**
     * Set the message to send
     */
    public function message(string|array $message): self
    {
        if (is_array($message)) {
            $message = http_build_query($message);
        }

        if (! str_starts_with($message, '?')) {
            $message = "?{$message}";
        }

        $this->message = $message;

        return $this;
    }

    /**
     * Set timeout for the connection
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
     * Send the message and wait for response
     */
    public function send(bool $wantResponse = true): GameBridgeResponse|Collection
    {
        if ($this->targets instanceof Collection && $this->targets->isEmpty()) {
            throw new RuntimeException('No targets specified for GameBridge connection');
        }

        if ($this->targets === null) {
            throw new RuntimeException('No targets specified for GameBridge connection');
        }

        if ($this->message === null) {
            throw new RuntimeException('No message specified for GameBridge connection');
        }

        $options = (object) [
            'message' => $this->message,
            'timeout' => $this->timeout,
            'force' => $this->force,
            'cacheFor' => $this->cacheFor,
            'wantResponse' => $wantResponse,
        ];

        // Handle single target
        if (! $this->targets instanceof Collection) {
            $response = $this->gameBridge->executeConnection($this->targets, $options);
            $response->setServerId($this->targets->getServerId());

            return $response;
        }

        // Handle multiple targets
        $responses = collect();

        foreach ($this->targets as $target) {
            $response = $this->gameBridge->executeConnection($target, $options);
            $response->setServerId($target->getServerId());
            $responses->add($response);
        }

        return $responses;
    }

    /**
     * Send the message without waiting for response
     */
    public function sendAndForget(): void
    {
        $this->send(false);
    }

    /**
     * Get the current targets
     */
    public function getTargets()
    {
        return $this->targets;
    }

    /**
     * Get the current message
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * Get the current timeout
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Check if force is enabled
     */
    public function isForced(): bool
    {
        return $this->force;
    }

    /**
     * Get cache duration
     */
    public function getCacheFor(): int
    {
        return $this->cacheFor;
    }
}
