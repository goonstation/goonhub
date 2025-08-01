<?php

namespace App\Http\Resources;

use App\Models\Player;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Player */
class PlayerResource extends JsonResource
{
    public $serverId = '';

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
            'ckey' => $this->ckey,
            'key' => $this->key,
            'byond_join_date' => $this->byond_join_date,
            'byond_major' => $this->byond_major,
            'byond_minor' => $this->byond_minor,
            /** @var bool */
            'is_admin' => $this->isAdmin,
            'admin_rank' => $this->whenLoaded('user', function () {
                return $this->user?->gameAdmin?->rank?->rank;
            }),
            /** @var bool */
            'is_mentor' => $this->isMentor,
            /** @var bool */
            'is_hos' => $this->isHos,
            'is_whitelisted' => $this->isWhitelisted($this->serverId),
            'can_bypass_cap' => $this->canBypassCap($this->serverId),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
