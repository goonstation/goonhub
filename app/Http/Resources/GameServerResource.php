<?php

namespace App\Http\Resources;

use App\Models\GameServer;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin GameServer */
class GameServerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'server_id' => $this->server_id,
            'name' => $this->name,
            'short_name' => $this->short_name,
            'address' => $this->address,
            'port' => $this->port,
            /** @var bool */
            'active' => $this->active,
            /** @var bool */
            'invisible' => $this->invisible,
            'group_id' => $this->group_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            /** @var int */
            'player_count' => $this->currentPlayersOnline->online ?? 0,
            /** @var int|null */
            'current_round_id' => $this->currentRound?->id,
            /** @var string|null */
            'current_map' => $this->when(
                (bool) $this->currentRound?->mapRecord,
                fn () => $this->currentRound->mapRecord->name,
                $this->when(
                    (bool) $this->gameBuildSetting?->map,
                    fn () => $this->gameBuildSetting->map->name,
                    null
                ),
            ),
        ];
    }
}
