<?php

namespace App\Enums\Permissions;

enum ErrorPermissions: string implements IsPermission
{
    use Permission;

    case VIEW = 'view-errors';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View Errors',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::VIEW => 'Can view game errors',
        };
    }
}
