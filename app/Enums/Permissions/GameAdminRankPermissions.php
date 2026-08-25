<?php

namespace App\Enums\Permissions;

enum GameAdminRankPermissions: string implements IsPermission
{
    use Permission;

    case VIEW = 'view-game-admin-ranks';
    case ADD = 'add-game-admin-ranks';
    case UPDATE = 'update-game-admin-ranks';
    case DELETE = 'delete-game-admin-ranks';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View Game Admin Ranks',
            self::ADD => 'Add Game Admin Ranks',
            self::UPDATE => 'Update Game Admin Ranks',
            self::DELETE => 'Delete Game Admin Ranks',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::VIEW => 'Can view game admin ranks',
            self::ADD => 'Can add game admin ranks',
            self::UPDATE => 'Can update game admin ranks',
            self::DELETE => 'Can delete game admin ranks',
        };
    }
}
