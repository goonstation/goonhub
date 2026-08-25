<?php

namespace App\Enums\Permissions;

enum RemoteMusicPermissions: string implements IsPermission
{
    use Permission;

    case ADD = 'add-remote-music';

    public function label(): string
    {
        return match ($this) {
            self::ADD => 'Add Remote Music',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ADD => 'Can queue remote music for a round',
        };
    }
}
