<?php

namespace App\Services\GameBridge;

use App\Helpers\SwooleStatus;
use App\Models\GameServer;
use Illuminate\Database\Eloquent\Collection as ModelCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Laravel\Octane\Facades\Octane;
use RuntimeException;

class GameBridgeService
{
    private int $timeout;

    private int $retryAttempts;

    private int $retryDelay;

    private int $defaultCacheTime;

    private int $maxTasks = 1;

    private array $pools = [
        'low' => 1,
        'medium' => 1,
        'high' => 1,
    ];

    public static array $priorities = [
        'low' => 1,
        'medium' => 2,
        'high' => 3,
    ];

    public function __construct()
    {
        $config = config('services.gamebridge', []);

        $this->timeout = $config['timeout'] ?? 5;
        $this->retryAttempts = $config['retry_attempts'] ?? 3;
        $this->retryDelay = $config['retry_delay'] ?? 1000; // milliseconds
        $this->defaultCacheTime = $config['default_cache_time'] ?? 30;

        if (SwooleStatus::isRunning()) {
            // 75% of the task workers
            $this->maxTasks = (int) round((SwooleStatus::getTaskWorkers() / 100) * 75);
            $this->pools['high'] = (int) round(($this->maxTasks / 100) * 25);
            $this->pools['medium'] = (int) round(($this->maxTasks / 100) * 25);
            $this->pools['low'] = $this->maxTasks - $this->pools['high'] - $this->pools['medium'];
        }
    }

    /**
     * Set the number of retry attempts
     */
    public function retryAttempts(int $retryAttempts): self
    {
        $this->retryAttempts = $retryAttempts;

        return $this;
    }

    /**
     * Disable retry attempts
     */
    public function noRetry(): self
    {
        $this->retryAttempts = 0;

        return $this;
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

    protected function isPoolAvailable(string $priority): bool
    {
        return (int) Cache::get("game_bridge_tasks_{$priority}") < $this->pools[$priority];
    }

    protected function getPriority(string $priority): ?string
    {
        // Higher priority tasks can steal pool slots from lower priority pools
        $poolIsAvailable = $this->isPoolAvailable($priority);
        while (! $poolIsAvailable && self::$priorities[$priority] > self::$priorities['low']) {
            $priority = array_search(self::$priorities[$priority] - 1, self::$priorities);
            $poolIsAvailable = $this->isPoolAvailable($priority);
        }

        return $poolIsAvailable ? $priority : null;
    }

    protected function executeSocket(Socket $socket): array
    {
        $socket->send();

        $response = $socket->wantResponse ? $socket->read() : '';
        $error = $socket->wantResponse ? $socket->error : false;

        $socket->disconnect();

        return compact('response', 'error');
    }

    /**
     * Execute a connection with retry logic and error handling
     */
    public function executeConnection(Target $target, object $options): GameBridgeResponse
    {
        $socket = new Socket($target->getAddress(), $target->getPort(), $options);

        $priority = in_array($options->priority, array_keys(self::$priorities)) ? $options->priority : 'medium';

        // Higher priority tasks can steal pool slots from lower priority pools
        // $poolIsAvailable = $this->isPoolAvailable($priority);
        // while (! $poolIsAvailable && self::$priorities[$priority] > self::$priorities['low']) {
        //     $priority = array_search(self::$priorities[$priority] - 1, self::$priorities);
        //     $poolIsAvailable = $this->isPoolAvailable($priority);
        // }

        // if (! $poolIsAvailable) {
        //     return new GameBridgeResponse('Unable to process request, please try again later. [1]', true, false);
        // }

        // Log::info('GameBridge tasks', ['tasks' => (int) Cache::get('game_bridge_tasks')]);
        // Log::info('GameBridge priority', ['priority' => $priority, 'priority_value' => self::$priorities[$priority]]);

        // if ((int) Cache::get('game_bridge_tasks') >= $this->maxTasks) {
        //     return new GameBridgeResponse('Unable to process request, please try again later. [2]', true, false);
        // }

        Cache::increment("game_bridge_tasks_{$priority}");
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
                // if (SwooleStatus::isRunning()) {
                //     if (! SwooleStatus::canDispatchTasks()) {
                //         throw new RuntimeException('Unable to process request, please try again later. [3]');
                //     }

                //     $dispatched = Octane::tasks()->resolve([
                //         'socket' => fn () => $this->executeSocket($socket),
                //     ], 30000);

                //     $task = $dispatched['socket'];
                // } else {
                //     $task = $this->executeSocket($socket);
                // }
                $task = $this->executeSocket($socket);

                $response = $task['response'];
                $error = $task['error'];

                if ($span !== null) {
                    $span->finish();
                    \Sentry\SentrySdk::getCurrentHub()->setSpan($parentSpan);
                }

                Cache::decrement("game_bridge_tasks_{$priority}");

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

                    $socket = new Socket($target->getAddress(), $target->getPort(), $options);
                }
            }
        }

        Cache::decrement("game_bridge_tasks_{$priority}");

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
