<?php

namespace App\Enums\Permissions;

enum PlayerMedalPermissions: string implements IsPermission
{
    use Permission;

    case VIEW = 'view-player-medals';
    case ADD = 'add-player-medals';
    case DELETE = 'delete-player-medals';
    case TRANSFER = 'transfer-player-medals';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View Player Medals',
            self::ADD => 'Add Player Medals',
            self::DELETE => 'Delete Player Medals',
            self::TRANSFER => 'Transfer Player Medals',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::VIEW => 'Can list and check player medals',
            self::ADD => 'Can add player medals',
            self::DELETE => 'Can delete player medals',
            self::TRANSFER => 'Can transfer medals between players',
        };
    }
}
