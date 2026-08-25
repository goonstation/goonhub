<?php

namespace App\Enums\Permissions;

enum EventPermissions: string implements IsPermission
{
    use Permission;

    case VIEW = 'view-events';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View Events',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::VIEW => 'Can view raw game events',
        };
    }
}
