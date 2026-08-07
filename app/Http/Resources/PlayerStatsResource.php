<?php

namespace App\Http\Resources;

use App\Models\Player;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Player
 *
 * @property int $played
 * @property int $played_rp
 * @property int $connected
 * @property int $connected_rp
 * @property int $time_played
 * @property int $played_security
 * @property int $played_medical
 * @property int $played_research
 * @property int $played_engineering
 */
class PlayerStatsResource extends JsonResource
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
            /** @var int */
            'id' => $this->id,
            'ckey' => $this->ckey,
            'key' => $this->key,
            'byond_join_date' => $this->byond_join_date,
            'byond_major' => $this->byond_major,
            'byond_minor' => $this->byond_minor,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            /** @var int */
            'played' => $this->played,
            /** @var int */
            'played_rp' => $this->played_rp,
            /** @var int */
            'played_security' => $this->played_security,
            /** @var int */
            'played_medical' => $this->played_medical,
            /** @var int */
            'played_research' => $this->played_research,
            /** @var int */
            'played_engineering' => $this->played_engineering,
            /** @var int */
            'connected' => $this->connected,
            /** @var int */
            'connected_rp' => $this->connected_rp,
            /** @var int */
            'time_played' => $this->time_played,
            /** @var PlayerConnectionResource */
            'latest_connection' => $this->latestConnection,
        ];
    }
}
