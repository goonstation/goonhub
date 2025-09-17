<?php

namespace App\Http\Resources;

use App\Models\Player;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Player */
class PlayerResource extends JsonResource
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
            'ckey' => $this->ckey,
            'key' => $this->key,
            'byond_join_date' => $this->byond_join_date,
            'byond_major' => $this->byond_major,
            'byond_minor' => $this->byond_minor,
            /** @var bool|null */
            'is_admin' => $this->whenAppended('is_admin'),
            'admin_rank' => $this->whenLoaded('user', function () {
                return $this->user?->gameAdmin?->rank?->rank;
            }),
            /** @var bool|null */
            'is_mentor' => $this->whenAppended('is_mentor'),
            /** @var bool|null */
            'is_hos' => $this->whenAppended('is_hos'),
            /** @var bool|null */
            'is_whitelisted' => $this->whenAppended('is_whitelisted'),
            /** @var bool|null */
            'can_bypass_cap' => $this->whenAppended('can_bypass_cap'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
