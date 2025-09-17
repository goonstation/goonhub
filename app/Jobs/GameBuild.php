<?php

namespace App\Jobs;

use App\Events\GameBuildCancelled;
use App\Events\GameBuildFinished;
use App\Events\GameBuildQueued;
use App\Events\GameBuildQueuedStarting;
use App\Events\GameBuildStarting;
use App\Libraries\GameBuilder\Build;
use App\Models\GameServer;
use App\Models\PlayerAdmin;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GameBuild implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    protected $timeout = 600;

    public $tries = 1;

    public $uniqueFor = 600;

    public PlayerAdmin $admin;

    public GameServer $server;

    public bool $mapSwitch = false;

    public string $queuedCacheKey = '';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(PlayerAdmin $admin, GameServer $server, ?bool $mapSwitch = false)
    {
        $this->admin = $admin;
        $this->server = $server;
        $this->mapSwitch = $mapSwitch;
        $this->queuedCacheKey = "GameBuild-{$this->server->server_id}-queued";

        $isBuilding = $this->isBuilding($this->server->server_id);
        if (! $isBuilding) {
            // Job is going to dispatch
            GameBuildStarting::dispatch($this->admin, $this->server, $this->mapSwitch, now());

        } elseif ($this->mapSwitch && ! Cache::has($this->queuedCacheKey)) {
            // Job is going to be blocked from dispatching, but we can queue it to run after
            GameBuildQueued::dispatch($this->admin, $this->server, $this->mapSwitch, now());

            Cache::set($this->queuedCacheKey, [
                'admin' => $this->admin,
                'mapSwitch' => $this->mapSwitch,
                'jobLockOwner' => $isBuilding,
                'pushedAt' => now(),
            ], $this->timeout);
        }
    }

    public static function isBuilding(string $serverId)
    {
        /** @var \Illuminate\Redis\Connections\PhpRedisConnection $conn */
        // @phpstan-ignore-next-line staticMethod.notFound
        $conn = Cache::lockConnection();
        $prefix = Cache::getPrefix();
        $key = "{$prefix}laravel_unique_job:".self::class.":$serverId";

        return $conn->get($key);
    }

    public static function isQueued(string $serverId)
    {
        return Cache::has("GameBuild-$serverId-queued");
    }

    public static function cancelQueuedBuild(string $serverId, int $adminId)
    {
        $cancelled = Cache::forget("GameBuild-$serverId-queued");

        if ($cancelled) {
            GameBuildCancelled::dispatch($serverId, $adminId, 'queued');
        }

        return $cancelled;
    }

    public function uniqueId(): string
    {
        return $this->server->server_id;
    }

    public function runQueuedBuild()
    {
        $queuedBuild = Cache::get($this->queuedCacheKey);
        if ($queuedBuild) {
            Cache::forget($this->queuedCacheKey);
            $lock = Cache::lock(
                'laravel_unique_job:'.self::class.':'.$this->uniqueId(),
                owner: $queuedBuild['jobLockOwner']
            );
            $lock->release();
            GameBuildQueuedStarting::dispatch($this->server->id);
            GameBuild::dispatch($this->admin, $this->server, true);
        }
    }

    private function onFinish()
    {
        GameBuildFinished::dispatch($this->server->id);
        Cache::forget("GameBuild-{$this->server->server_id}-build");
        $this->runQueuedBuild();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $build = new Build($this->server, $this->admin, $this->mapSwitch);
        $build->start();
        $this->onFinish();
    }

    public function failed(?\Throwable $exception): void
    {
        Log::alert($exception->getMessage());
        $this->onFinish();
    }
}
