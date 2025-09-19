<?php

namespace App\Services\GameBridge;

use App\Models\GameServer;
use Illuminate\Database\Eloquent\Collection as ModelCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GameBridgeService
{
    private int $timeout;

    private int $retryAttempts;

    private int $retryDelay;

    private int $defaultCacheTime;

    public function __construct()
    {
        $config = config('services.gamebridge', []);

        $this->timeout = $config['timeout'] ?? 5;
        $this->retryAttempts = $config['retry_attempts'] ?? 3;
        $this->retryDelay = $config['retry_delay'] ?? 1000; // milliseconds
        $this->defaultCacheTime = $config['default_cache_time'] ?? 30;
    }

    /**
     * Get a server-specific API instance for fluent operations
     */
    public function server(string|GameServer $server): Server
    {
        return new Server($this, $server);
    }

    /**
     * Get multiple servers for batch operations
     */
    public function servers(array|ModelCollection $servers): ServerCollection
    {
        return new ServerCollection($this, $servers);
    }

    /**
     * Create a raw connection for advanced usage
     */
    public function connection(): Connection
    {
        return new Connection($this);
    }

    /**
     * Resolve a single server target to a standardized format
     */
    public function resolveTarget(string|GameServer $target): Target
    {
        if (! $target) {
            throw new RuntimeException('Invalid target');
        }

        if ($target instanceof GameServer) {
            return Target::fromGameServer($target);
        }

        // String server ID - resolve to GameServer
        $server = GameServer::select(['server_id', 'address', 'port'])
            ->where('server_id', $target)
            ->firstOrFail();

        return Target::fromGameServer($server);
    }

    /**
     * Resolve multiple server targets to a standardized format
     */
    public function resolveTargets(array|ModelCollection $targets): Collection
    {
        if (is_array($targets) && empty($targets)) {
            throw new RuntimeException('Invalid targets');
        }

        $resolved = collect();

        if ($targets instanceof ModelCollection) {
            /** @var GameServer $server */
            foreach ($targets as $server) {
                $resolved->add(Target::fromGameServer($server));
            }
        } else {
            // Array of server IDs
            $servers = GameServer::select(['server_id', 'address', 'port'])
                ->whereIn('server_id', $targets)
                ->get();

            /** @var GameServer $server */
            foreach ($servers as $server) {
                $resolved->add(Target::fromGameServer($server));
            }
        }

        return $resolved;
    }

    /**
     * Execute a connection with retry logic and error handling
     */
    public function executeConnection(Target $target, object $options): GameBridgeResponse
    {
        $parentSpan = \Sentry\SentrySdk::getCurrentHub()->getSpan();
        $span = null;

        if ($parentSpan !== null) {
            $span = $parentSpan->startChild(\Sentry\Tracing\SpanContext::make()->setOp('game_bridge'));
            \Sentry\SentrySdk::getCurrentHub()->setSpan($span);
        }

        $lastException = null;
        $attempt = 0;

        while ($attempt <= $this->retryAttempts) {
            try {
                $socket = new Socket($target->getAddress(), $target->getPort(), $options);
                $response = '';
                $error = false;

                $socket->send();
                if ($socket->wantResponse) {
                    $response = $socket->read();
                    $error = $socket->error;
                }

                $socket->disconnect();

                if ($span !== null) {
                    $span->finish();
                    \Sentry\SentrySdk::getCurrentHub()->setSpan($parentSpan);
                }

                return new GameBridgeResponse($response, $error, $socket->cacheHit);

            } catch (\Throwable $e) {
                $lastException = $e;
                $attempt++;

                if ($attempt <= $this->retryAttempts) {
                    Log::warning("GameBridge connection failed, retrying in {$this->retryDelay}ms", [
                        'address' => $target->getAddress(),
                        'port' => $target->getPort(),
                        'attempt' => $attempt,
                        'error' => $e->getMessage(),
                    ]);

                    usleep($this->retryDelay * 1000); // Convert to microseconds
                }
            }
        }

        if ($span !== null) {
            $span->finish();
            \Sentry\SentrySdk::getCurrentHub()->setSpan($parentSpan);
        }

        if ($lastException && $options->wantResponse) {
            return new GameBridgeResponse($lastException->getMessage(), true, false);
        }

        throw $lastException;
    }

    /**
     * Get configuration values
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function getRetryAttempts(): int
    {
        return $this->retryAttempts;
    }

    public function getRetryDelay(): int
    {
        return $this->retryDelay;
    }

    public function getDefaultCacheTime(): int
    {
        return $this->defaultCacheTime;
    }
}
