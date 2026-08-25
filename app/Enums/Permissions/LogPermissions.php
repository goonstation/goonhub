<?php

namespace App\Enums\Permissions;

enum LogPermissions: string implements IsPermission
{
    use Permission;

    case VIEW = 'view-logs';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View Logs',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::VIEW => 'Can view game logs',
        };
    }
}
