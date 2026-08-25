<?php

namespace App\Enums\Permissions;

enum VpnWhitelistPermissions: string implements IsPermission
{
    use Permission;

    case VIEW = 'view-vpn-whitelist';
    case ADD = 'add-vpn-whitelist';
    case DELETE = 'delete-vpn-whitelist';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View VPN Whitelist',
            self::ADD => 'Add VPN Whitelist',
            self::DELETE => 'Delete VPN Whitelist',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::VIEW => 'Can view VPN whitelist entries',
            self::ADD => 'Can add VPN whitelist entries',
            self::DELETE => 'Can delete VPN whitelist entries',
        };
    }
}
