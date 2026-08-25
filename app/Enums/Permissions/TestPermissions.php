<?php

namespace App\Enums\Permissions;

enum TestPermissions: string implements IsPermission
{
    use Permission;

    case VIEW = 'view-test';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View Test',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::VIEW => 'Can view test',
        };
    }
}
