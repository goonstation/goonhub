<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permissions\AuditPermissions;
use App\Enums\Permissions\TestPermissions;
use App\Enums\Roles;
use App\Facades\GameBridge;
use App\Helpers\HasAnyAbility;
use App\Helpers\SwooleStatus;
use App\Http\Controllers\Controller;
use App\Models\GameServer;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Collection;
use Laravel\Octane\Facades\Octane;
use Spatie\Permission\Models\Role;

class TestController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            // HasAnyAbility::using(TestPermissions::VIEW, only: ['index']),
        ];
    }

    /**
     * Test
     */
    public function index(Request $request)
    {
        // $siteAdminRole = Role::findByName(Roles::SITE_ADMIN->value, 'web');
        // $siteAdminRole->revokePermissionTo(TestPermissions::VIEW);

        // $user->assignRole(Roles::SITE_ADMIN);
        // $user->removeRole(Roles::TEST);
        // $user->revokePermissionTo(AuditPermissions::VIEW);

        // $task = Octane::tasks()->resolve([
        //     'execute' => function () {
        //         return GameBridge::retryAttempts(1)->server('dev')->status();
        //     },
        // ]);
        // $status = $task['execute']->getData();

        // dispatch(function () {
        //     $task = Octane::tasks()->resolve([
        //         'execute' => function () {
        //             sleep(25);
        //             // return GameBridge::retryAttempts(1)->server('dev')->status();
        //         },
        //     ], 30000);

        //     return $task['execute'];
        // });

        // $status = GameBridge::noRetry()->server('dev')->status();

        $servers = GameServer::where('active', true)->where('group_id', 1)->where('server_id', 'dev')->get();

        $taskWorkers = SwooleStatus::getTaskWorkers();
        $chunks = $servers->chunk($taskWorkers);
        $timeout = 100 / $chunks->count(); // 100 seconds max timeout

        $statuses = $chunks->flatMap(function (Collection $chunk) use ($timeout) {
            return Octane::concurrently($chunk->mapWithKeys(function (GameServer $server) use ($timeout) {
                $serverId = $server->server_id;

                return [$serverId => function () use ($serverId, $timeout) {
                    return GameBridge::noRetry()
                        ->server($serverId)
                        ->priority('high')
                        ->timeout($timeout)
                        ->status();
                }];
            })->toArray(), $timeout * 1000);
        });

        // $status = GameBridge::noRetry()
        //     ->server('dev')
        //     ->priority('high')
        //     ->timeout($timeout)
        //     ->status();

        return response()->json([
            // 'statuses' => $statuses,
            // 'status' => $status->getData(),
            // 'message' => 'Hello, world!',
            // 'status' => $status->message,
            // 'token_permissions' => $token->abilities,
            // 'user_permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    public function status()
    {
        return SwooleStatus::getStats();
    }
}
