<?php

namespace App\Enums\Permissions;

enum BanPermissions: string implements IsPermission
{
    use Permission;

    case VIEW = 'view-bans';
    case ADD = 'add-bans';
    case UPDATE = 'update-bans';
    case DELETE = 'delete-bans';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View Bans',
            self::ADD => 'Add Bans',
            self::UPDATE => 'Update Bans',
            self::DELETE => 'Delete Bans',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::VIEW => 'Can view bans and check player ban status',
            self::ADD => 'Can add bans and add ban details',
            self::UPDATE => 'Can update bans',
            self::DELETE => 'Can delete bans and delete ban details',
        };
    }
}
