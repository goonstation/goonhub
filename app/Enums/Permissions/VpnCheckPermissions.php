<?php

namespace App\Enums\Permissions;

enum VpnCheckPermissions: string implements IsPermission
{
    use Permission;

    case CHECK = 'check-vpn';

    public function label(): string
    {
        return match ($this) {
            self::CHECK => 'Check VPN',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::CHECK => 'Can check a player for VPN usage',
        };
    }
}
