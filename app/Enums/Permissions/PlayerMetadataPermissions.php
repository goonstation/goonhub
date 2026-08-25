<?php

namespace App\Enums\Permissions;

enum PlayerMetadataPermissions: string implements IsPermission
{
    use Permission;

    case VIEW = 'view-player-metadata';
    case ADD = 'add-player-metadata';
    case DELETE = 'delete-player-metadata';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View Player Metadata',
            self::ADD => 'Add Player Metadata',
            self::DELETE => 'Delete Player Metadata',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::VIEW => 'Can view and fetch player metadata',
            self::ADD => 'Can add player metadata',
            self::DELETE => 'Can delete player metadata',
        };
    }
}
