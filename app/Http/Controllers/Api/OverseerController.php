<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OverseerStatusResource;
use App\Traits\ManagesOverseer;
use Illuminate\Http\Request;

class OverseerController extends Controller
{
    use ManagesOverseer;

    /**
     * Status
     *
     * Get the status of one or all game servers
     */
    public function status(Request $request)
    {
        try {
            /** @var array<string, OverseerStatusResource> */
            return OverseerStatusResource::collection($this->getServerStatus($request));
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
            $operation = $this->restartServer($request);
            $operation['statusUrl'] = route('api.overseer.operation', ['id' => $operation['id'], 'server' => $operation['server']]);

            return response()->json($operation, 202)->header('Location', $operation['statusUrl']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function operation(Request $request, string $id)
    {
        try {
            return $this->getRestartOperation($request, $id);
        } catch (\Throwable $exception) {
            return response()->json(['message' => $exception->getMessage()], 500);
        }
    }
}
