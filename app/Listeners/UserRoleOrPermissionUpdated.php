<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Events\PermissionAttached;
use Spatie\Permission\Events\PermissionDetached;
use Spatie\Permission\Events\RoleAttached;
use Spatie\Permission\Events\RoleDetached;

class UserRoleOrPermissionUpdated
{
    public function subscribe(Dispatcher $events): array
    {
        return [
            PermissionAttached::class => 'handlePermissionAttached',
            PermissionDetached::class => 'handlePermissionDetached',
            RoleAttached::class => 'handleRoleAttached',
            RoleDetached::class => 'handleRoleDetached',
        ];
    }

    public function handle(PermissionAttached|PermissionDetached|RoleAttached|RoleDetached $event)
    {
        if (! $event->model instanceof User) {
            return;
        }

        Cache::forget('user_permissions_'.$event->model->id);
        Cache::forget('user_roles_'.$event->model->id);
    }
}
