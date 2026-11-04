<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OverseerStatusResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'status' => $this['status'],
            'health' => $this['health'] ?? null,
            /** @format date-time */
            'startedAt' => $this['startedAt'] ?? null,
            /** @var bool */
            'restarting' => $this['restarting'] ?? false,
        ];
    }
}
