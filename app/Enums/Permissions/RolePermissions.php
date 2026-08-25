<?php

namespace App\Enums\Permissions;

enum RolePermissions: string implements IsPermission
{
    use Permission;

    case VIEW = 'view-roles';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View Roles',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::VIEW => 'Can view roles',
        };
    }
}
