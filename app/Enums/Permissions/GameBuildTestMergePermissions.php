<?php

namespace App\Enums\Permissions;

enum GameBuildTestMergePermissions: string implements IsPermission
{
    use Permission;

    case VIEW = 'view-game-build-test-merges';
    case ADD = 'add-game-build-test-merges';
    case UPDATE = 'update-game-build-test-merges';
    case DELETE = 'delete-game-build-test-merges';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View Game Build Test Merges',
            self::ADD => 'Add Game Build Test Merges',
            self::UPDATE => 'Update Game Build Test Merges',
            self::DELETE => 'Delete Game Build Test Merges',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::VIEW => 'Can view game build test merges',
            self::ADD => 'Can add game build test merges',
            self::UPDATE => 'Can update game build test merges',
            self::DELETE => 'Can delete game build test merges',
        };
    }
}
