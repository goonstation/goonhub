<?php

namespace App\Http\Resources;

use App\Models\BanDetail;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BanDetail
 *
 * @property BanDetailResource|null $originalBanDetail
 */
class BanDetailResource extends JsonResource
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
            'ban_id' => $this->ban_id,
            'ckey' => $this->ckey,
            'comp_id' => $this->comp_id,
            'ip' => $this->ip,
            'player_id' => $this->player_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            /** @var BanDetailResource|null */
            'original_ban_detail' => $this->when(! is_null($this->originalBanDetail), $this->originalBanDetail),
        ];
    }
}
