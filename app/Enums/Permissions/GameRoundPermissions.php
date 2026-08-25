<?php

namespace App\Enums\Permissions;

enum GameRoundPermissions: string implements IsPermission
{
    use Permission;

    case VIEW = 'view-game-rounds';
    case ADD = 'add-game-rounds';
    case UPDATE = 'update-game-rounds';
    case END = 'end-game-rounds';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View Game Rounds',
            self::ADD => 'Add Game Rounds',
            self::UPDATE => 'Update Game Rounds',
            self::END => 'End Game Rounds',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::VIEW => 'Can view game rounds',
            self::ADD => 'Can start new game rounds',
            self::UPDATE => 'Can update game rounds',
            self::END => 'Can end game rounds',
        };
    }
}
