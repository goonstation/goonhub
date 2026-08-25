<?php

namespace App\Enums\Permissions;

enum HosPermissions: string implements IsPermission
{
    use Permission;

    case UPDATE = 'update-hos';

    public function label(): string
    {
        return match ($this) {
            self::UPDATE => 'Update Heads of Security',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::UPDATE => 'Can add or remove players from Heads of Security (single or bulk)',
        };
    }
}
