<?php

namespace App\Http\Resources;

use App\Models\GameBuild;
use App\Models\GameServer;
use App\Models\PlayerAdmin;
use Illuminate\Http\Resources\Json\JsonResource;

class GameBuildStatusCurrentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            /** @var PlayerAdmin */
            'admin' => $this['admin'],
            /** @var GameServer */
            'server' => $this['server'],
            /** @var GameBuild */
            'build' => $this['build'],
            /** @var bool */
            'mapSwitch' => $this['mapSwitch'],
            /** @format date-time */
            'startedAt' => $this['startedAt'],
            /** @var bool */
            'cancelled' => $this['cancelled'],
        ];
    }
}
