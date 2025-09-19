<?php

namespace App\Jobs;

use App\Facades\GameBridge;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PrintGameMysteryFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $serverId = '';

    private $title = '';

    private $file = '';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $serverId, string $title, string $file)
    {
        $this->serverId = $serverId;
        $this->title = $title;
        $this->file = $file;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        GameBridge::server($this->serverId)
            ->force(true)
            ->sendAndForget([
                'type' => 'mysteryPrint',
                'print_title' => $this->title,
                'print_file' => $this->file,
            ]);
    }
}
