<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Traits\ManagesOverseer;
use Illuminate\Http\Request;

class OverseerController extends Controller
{
    use ManagesOverseer;

    public function status(Request $request)
    {
        try {
            return $this->getServerStatus($request);
        } catch (\Throwable $e) {
            return abort(500, $e->getMessage() ?: 'Something went wrong');
        }
    }

    public function restart(Request $request)
    {
        try {
            $operation = $this->restartServer($request);
            $operation['statusUrl'] = route('admin.overseer.operation', ['id' => $operation['id'], 'server' => $operation['server']]);

            return response()->json($operation, 202)->header('Location', $operation['statusUrl']);
        } catch (\Throwable $e) {
            return abort(500, $e->getMessage() ?: 'Something went wrong');
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
