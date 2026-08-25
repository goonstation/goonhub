<?php

namespace App\Enums\Permissions;

enum PlayerAntagPermissions: string implements IsPermission
{
    use Permission;

    case ADD = 'add-player-antags';

    public function label(): string
    {
        return match ($this) {
            self::ADD => 'Add Player Antags',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ADD => 'Can add player antags',
        };
    }
}
