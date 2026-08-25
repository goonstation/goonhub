<?php

namespace App\Enums\Permissions;

enum GameBuildPermissions: string implements IsPermission
{
    use Permission;

    case VIEW = 'view-game-builds';
    case BUILD = 'build-game';
    case CANCEL = 'cancel-game-builds';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View Game Builds',
            self::BUILD => 'Run Game Builds',
            self::CANCEL => 'Cancel Game Builds',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::VIEW => 'Can view and check game build status',
            self::BUILD => 'Can trigger new game builds',
            self::CANCEL => 'Can cancel running or queued game builds',
        };
    }
}
