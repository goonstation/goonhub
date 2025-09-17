<?php

namespace App\Http\Resources;

use App\Models\PlayerNote;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PlayerNote */
class PlayerNoteResource extends JsonResource
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
            'player_id' => $this->player_id,
            'player' => new PlayerResource($this->whenLoaded('player')),
            'ckey' => $this->ckey,
            'game_admin_id' => $this->game_admin_id,
            'game_admin' => new PlayerAdminResource($this->whenLoaded('gameAdmin')),
            'server_id' => $this->server_id,
            'round_id' => $this->round_id,
            'note' => $this->note,
            'legacy_data' => $this->legacy_data,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
