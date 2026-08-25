<?php

namespace App\Enums\Permissions;

enum GauntletPermissions: string implements IsPermission
{
    use Permission;

    case VIEW = 'view-gauntlets';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View Gauntlets',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::VIEW => 'Can view gauntlets',
        };
    }
}
