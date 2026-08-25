<?php

namespace App\Enums\Permissions;

enum GameAdminPermissions: string implements IsPermission
{
    use Permission;

    case VIEW = 'view-game-admins';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View Game Admins',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::VIEW => 'Can view game admins',
        };
    }
}
