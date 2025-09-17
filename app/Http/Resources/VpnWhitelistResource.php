<?php

namespace App\Http\Resources;

use App\Models\VpnWhitelist;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin VpnWhitelist */
class VpnWhitelistResource extends JsonResource
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
            'game_admin_id' => $this->game_admin_id,
            'ckey' => $this->ckey,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'game_admin' => new PlayerAdminResource($this->whenLoaded('gameAdmin')),
        ];
    }
}
