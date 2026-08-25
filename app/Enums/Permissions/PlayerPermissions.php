<?php

namespace App\Enums\Permissions;

enum PlayerPermissions: string implements IsPermission
{
    use Permission;

    case LOGIN = 'login-players';
    case VIEW = 'view-players';
    case STATS = 'view-player-stats';
    case IPS = 'view-player-ips';
    case COMP_IDS = 'view-player-comp-ids';

    public function label(): string
    {
        return match ($this) {
            self::LOGIN => 'Login Players',
            self::VIEW => 'View Players',
            self::STATS => 'View Player Stats',
            self::IPS => 'View Player IPs',
            self::COMP_IDS => 'View Player Computer IDs',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::LOGIN => 'Can register player logins',
            self::VIEW => 'Can view player details',
            self::STATS => 'Can view player statistics',
            self::IPS => 'Can view player IPs',
            self::COMP_IDS => 'Can view player computer IDs',
        };
    }
}
