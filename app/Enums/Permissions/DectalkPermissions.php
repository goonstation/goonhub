<?php

namespace App\Enums\Permissions;

enum DectalkPermissions: string implements IsPermission
{
    use Permission;

    case ADD = 'add-dectalk';

    public function label(): string
    {
        return match ($this) {
            self::ADD => 'Run Dectalk',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ADD => 'Can generate dectalk audio',
        };
    }
}
