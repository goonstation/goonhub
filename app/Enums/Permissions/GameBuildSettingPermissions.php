<?php

namespace App\Enums\Permissions;

enum GameBuildSettingPermissions: string implements IsPermission
{
    use Permission;

    case VIEW = 'view-game-build-settings';
    case ADD = 'add-game-build-settings';
    case UPDATE = 'update-game-build-settings';
    case DELETE = 'delete-game-build-settings';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View Game Build Settings',
            self::ADD => 'Add Game Build Settings',
            self::UPDATE => 'Update Game Build Settings',
            self::DELETE => 'Delete Game Build Settings',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::VIEW => 'Can view game build settings',
            self::ADD => 'Can add game build settings',
            self::UPDATE => 'Can update game build settings',
            self::DELETE => 'Can delete game build settings',
        };
    }
}


