<?php

namespace App\Enums\Permissions;

enum MedalPermissions: string implements IsPermission
{
    use Permission;

    case VIEW = 'view-medals';
    case ADD = 'add-medals';
    case UPDATE = 'update-medals';
    case DELETE = 'delete-medals';
    case ADD_TO_PLAYER = 'add-medals-to-player';
    case REMOVE_FROM_PLAYER = 'remove-medals-from-player';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View Medals',
            self::ADD => 'Add Medals',
            self::UPDATE => 'Update Medals',
            self::DELETE => 'Delete Medals',
            self::ADD_TO_PLAYER => 'Add Medals to Player',
            self::REMOVE_FROM_PLAYER => 'Remove Medals from Player',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::VIEW => 'Can view medals',
            self::ADD => 'Can create medals',
            self::UPDATE => 'Can update medals',
            self::DELETE => 'Can delete medals',
            self::ADD_TO_PLAYER => 'Can add medals to players',
            self::REMOVE_FROM_PLAYER => 'Can remove medals from players',
        };
    }
}
