<?php

namespace App\Enums\Permissions;

enum PlayerSavePermissions: string implements IsPermission
{
    use Permission;

    case VIEW = 'view-player-saves';
    case ADD = 'add-player-saves';
    case DELETE = 'delete-player-saves';
    case TRANSFER = 'transfer-player-saves';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View Player Data and Saves',
            self::ADD => 'Add Player Data and Saves',
            self::DELETE => 'Delete Player Data and Saves',
            self::TRANSFER => 'Transfer Player Saves',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::VIEW => 'Can list and fetch player data and saves',
            self::ADD => 'Can add player data or saves',
            self::DELETE => 'Can delete player data or saves',
            self::TRANSFER => 'Can transfer saves between players',
        };
    }
}
