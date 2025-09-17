<?php

namespace App\Http\Resources;

use App\Models\PlayerAdmin;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PlayerAdmin */
class PlayerAdminResource extends JsonResource
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
            'alias' => $this->alias,
            'rank' => new GameAdminRankResource($this->whenLoaded('rank')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
