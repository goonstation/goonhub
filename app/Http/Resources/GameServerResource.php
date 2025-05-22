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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
