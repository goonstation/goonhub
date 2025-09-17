<?php

namespace App\Events;

use App\Models\PlayerAdmin;
use App\Models\GameServer;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class GameBuildStarting implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $queue = 'events';

    /**
     * Create a new event instance.
     */
    public function __construct(
        public PlayerAdmin $admin,
        public GameServer $server,
        public bool $mapSwitch,
        public Carbon $startedAt
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('game-builds'),
        ];
    }
}
