<?php

namespace App\Enums\Permissions;

enum RedirectPermissions: string implements IsPermission
{
    use Permission;

    case VIEW = 'view-redirects';
    case ADD = 'add-redirects';
    case UPDATE = 'update-redirects';
    case DELETE = 'delete-redirects';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View Redirects',
            self::ADD => 'Add Redirects',
            self::UPDATE => 'Update Redirects',
            self::DELETE => 'Delete Redirects',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::VIEW => 'Can view redirects',
            self::ADD => 'Can add redirects',
            self::UPDATE => 'Can update redirects',
            self::DELETE => 'Can delete redirects',
        };
    }
}
