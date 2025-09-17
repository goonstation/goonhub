<?php

namespace App\Http\Resources;

use App\Models\JobBan;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin JobBan */
class JobBanResource extends JsonResource
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
            'ckey' => $this->ckey,
            'banned_from_job' => $this->banned_from_job,
            'reason' => $this->reason,
            'expires_at' => $this->expires_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'game_admin' => new PlayerAdminResource($this->whenLoaded('gameAdmin')),
        ];
    }
}
