<?php

namespace App\Enums\Permissions;

enum WhitelistPermissions: string implements IsPermission
{
    use Permission;

    case UPDATE = 'update-whitelist';

    public function label(): string
    {
        return match ($this) {
            self::UPDATE => 'Update Whitelist',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::UPDATE => 'Can add or remove players from whitelist (single or bulk)',
        };
    }
}
