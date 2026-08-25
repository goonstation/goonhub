<?php

namespace App\Enums\Permissions;

enum UserPermissions: string implements IsPermission
{
    use Permission;

    case VIEW = 'view-users';
    case ADD = 'add-users';
    case UPDATE = 'update-users';
    case DELETE = 'delete-users';
    case LINK_DISCORD = 'link-users-discord';
    case UNLINK_DISCORD = 'unlink-users-discord';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View Users',
            self::ADD => 'Add Users',
            self::UPDATE => 'Update Users',
            self::DELETE => 'Delete Users',
            self::LINK_DISCORD => 'Link Discord Users',
            self::UNLINK_DISCORD => 'Unlink Discord Users',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::VIEW => 'Can view users',
            self::ADD => 'Can add users',
            self::UPDATE => 'Can update users',
            self::DELETE => 'Can delete users',
            self::LINK_DISCORD => 'Can link Discord and BYOND users',
            self::UNLINK_DISCORD => 'Can unlink Discord and BYOND users',
        };
    }
}
