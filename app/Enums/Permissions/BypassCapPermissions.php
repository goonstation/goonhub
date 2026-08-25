<?php

namespace App\Enums\Permissions;

enum BypassCapPermissions: string implements IsPermission
{
    use Permission;

    case UPDATE = 'update-bypass-cap';

    public function label(): string
    {
        return match ($this) {
            self::UPDATE => 'Update Bypass Cap',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::UPDATE => 'Can add or remove players from bypass cap (single or bulk)',
        };
    }
}
