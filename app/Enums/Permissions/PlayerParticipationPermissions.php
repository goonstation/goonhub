<?php

namespace App\Enums\Permissions;

enum PlayerParticipationPermissions: string implements IsPermission
{
    use Permission;

    case ADD = 'add-player-participations';

    public function label(): string
    {
        return match ($this) {
            self::ADD => 'Add Player Participations',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ADD => 'Can add player participations (single or bulk)',
        };
    }
}
