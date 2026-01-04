<?php

namespace App\Jobs;

use App\Facades\GameBridge;
use App\Models\GameServer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GetGameStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 30;

    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public GameServer $server,
    ) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $status = GameBridge::noRetry()
            ->server($this->server)
            ->priority('high')
            ->timeout(25)
            ->status();

        if ($status->failed()) {
            Log::error('GameBridge error getting status', [
                'server_id' => $this->server->server_id,
                'message' => $status->getMessage(),
            ]);

            return;
        }

        Cache::put("game_status_{$this->server->server_id}", $status->getData(), 60);
    }
}
