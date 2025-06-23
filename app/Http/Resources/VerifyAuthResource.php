<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class VerifyAuthResource extends JsonResource
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
            'player_id' => $this->player->id,
            'ckey' => $this->player->ckey,
            'key' => $this->player->key,
            'is_admin' => $this->isGameAdmin(),
            'admin_rank' => $this->when($this->isGameAdmin(), function () {
                return $this->gameAdmin->rank->rank;
            }, null),
            'is_mentor' => $this->player->isMentor,
            'is_hos' => $this->player->isHos,
            'is_whitelisted' => $this->player->isWhitelisted($request->input('server_id')),
            'can_bypass_cap' => $this->player->canBypassCap($request->input('server_id')),
        ];
    }
}
