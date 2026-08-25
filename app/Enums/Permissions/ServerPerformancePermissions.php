<?php

namespace App\Enums\Permissions;

enum ServerPerformancePermissions: string implements IsPermission
{
    use Permission;

    case VIEW = 'view-server-performance';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View Server Performance',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::VIEW => 'Can view server performance metrics',
        };
    }
}
