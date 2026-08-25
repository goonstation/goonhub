<?php

namespace App\Enums\Permissions;

enum RandomEntryPermissions: string implements IsPermission
{
    use Permission;

    case VIEW = 'view-random-entries';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View Random Entries',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::VIEW => 'Can view random entries',
        };
    }
}
