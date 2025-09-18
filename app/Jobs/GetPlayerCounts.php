<?php

namespace App\Jobs;

use App\Facades\GameBridge;
use App\Models\GameServer;
use App\Models\PlayersOnline;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class GetPlayerCounts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Get player count for all active servers (including invisible ones so their counts are tracked)
        $servers = GameServer::where('active', true)->get();

        $when = Carbon::now();
        foreach ($servers as $server) {
            $response = GameBridge::create()
                ->target($server)
                ->force(true)
                ->message('players')
                ->send();
            $playerCount = $response->error ? null : (int) $response->message;
            $playersOnline = new PlayersOnline;
            $playersOnline->timestamps = false;
            $playersOnline->server_id = $server->server_id;
            $playersOnline->online = $playerCount;
            $playersOnline->created_at = $when;
            $playersOnline->save();
        }
    }
}
