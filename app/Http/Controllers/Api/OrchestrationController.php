<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permissions\OrchestrationPermissions;
use App\Helpers\HasAnyAbility;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrchestrationStatusResource;
use App\Traits\ManagesOrchestration;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class OrchestrationController extends Controller implements HasMiddleware
{
    use ManagesOrchestration;

    public static function middleware(): array
    {
        return [
            HasAnyAbility::using(OrchestrationPermissions::VIEW, only: ['status']),
            HasAnyAbility::using(OrchestrationPermissions::RESTART, only: ['restart']),
        ];
    }

    /**
     * Status
     *
     * Get the status of one or all game servers
     */
    public function status(Request $request)
    {
        try {
            /** @var array<string, OrchestrationStatusResource> */
            return OrchestrationStatusResource::collection($this->getServerStatus($request));
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Restart
     *
     * Restart a game server
     */
    public function restart(Request $request)
    {
        try {
            $this->restartServer($request);

            return ['message' => 'Success'];
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
