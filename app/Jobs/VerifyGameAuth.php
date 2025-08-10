<?php

namespace App\Jobs;

use App\Facades\GameBridge;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class VerifyGameAuth implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public string $serverId,
        public array $message,
    ) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $response = GameBridge::create()
            ->target($this->serverId)
            ->message($this->message)
            ->force(true)
            ->send();

        if ($response->error) {
            throw new \Exception('GameBridge error: '.$response->message);
        }
    }
}
