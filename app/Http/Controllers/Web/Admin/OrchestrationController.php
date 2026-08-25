<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\Permissions\OrchestrationPermissions;
use App\Helpers\HasPermission;
use App\Http\Controllers\Controller;
use App\Traits\ManagesOrchestration;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class OrchestrationController extends Controller implements HasMiddleware
{
    use ManagesOrchestration;

    public static function middleware(): array
    {
        return [
            HasPermission::using(OrchestrationPermissions::VIEW, only: ['status']),
            HasPermission::using(OrchestrationPermissions::RESTART, only: ['restart']),
        ];
    }

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
            $this->restartServer($request);

            return ['message' => 'Success'];
        } catch (\Throwable $e) {
            return abort(500, $e->getMessage() ?: 'Something went wrong');
        }
    }
}
