<?php

namespace App\Http\Resources;

use App\Models\Ban;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Ban */
class BanResource extends JsonResource
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
            'round_id' => $this->round_id,
            'game_admin_id' => $this->game_admin_id,
            'server_id' => $this->server_id,
            'server_group' => $this->server_group,
            'reason' => $this->reason,
            /** @var bool */
            'active' => $this->active,
            /** @var int */
            'duration' => $this->duration,
            /** @var string|null */
            'duration_human' => $this->duration_human,
            'requires_appeal' => $this->requires_appeal,
            'expires_at' => $this->expires_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'game_admin' => new PlayerAdminResource($this->gameAdmin),
            'game_round' => new GameRoundResource($this->whenLoaded('gameRound')),
            'original_ban_detail' => new BanDetailResource($this->whenLoaded('originalBanDetail')),
            'details' => BanDetailResource::collection($this->whenLoaded('details')),
        ];
    }
}
