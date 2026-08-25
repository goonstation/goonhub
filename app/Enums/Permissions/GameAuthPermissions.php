<?php

namespace App\Enums\Permissions;

enum GameAuthPermissions: string implements IsPermission
{
    use Permission;

    case BEGIN = 'begin-game-auth';

    public function label(): string
    {
        return match ($this) {
            self::BEGIN => 'Begin Game Auth',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::BEGIN => 'Can begin a game auth session',
        };
    }
}
