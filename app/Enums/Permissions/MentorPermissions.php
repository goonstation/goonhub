<?php

namespace App\Enums\Permissions;

enum MentorPermissions: string implements IsPermission
{
    use Permission;

    case UPDATE = 'update-mentors';

    public function label(): string
    {
        return match ($this) {
            self::UPDATE => 'Update Mentors',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::UPDATE => 'Can add or remove players from Mentors (single or bulk)',
        };
    }
}
