<?php

namespace App\Enums\Permissions;

enum PlayerPlaytimePermissions: string implements IsPermission
{
    use Permission;

    case ADD = 'add-player-playtime';

    public function label(): string
    {
        return match ($this) {
            self::ADD => 'Add Player Playtime',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ADD => 'Can add player playtime (single or bulk)',
        };
    }
}
