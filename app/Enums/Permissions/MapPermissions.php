<?php

namespace App\Enums\Permissions;

enum MapPermissions: string implements IsPermission
{
    use Permission;

    case GENERATE = 'generate-maps';
    case VIEW = 'view-maps';
    case ADD = 'add-maps';
    case UPDATE = 'update-maps';
    case DELETE = 'delete-maps';

    public function label(): string
    {
        return match ($this) {
            self::GENERATE => 'Generate Maps',
            self::VIEW => 'View Maps',
            self::ADD => 'Add Maps',
            self::UPDATE => 'Update Maps',
            self::DELETE => 'Delete Maps',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::GENERATE => 'Can upload and process map images',
            self::VIEW => 'Can view maps',
            self::ADD => 'Can add maps',
            self::UPDATE => 'Can update maps',
            self::DELETE => 'Can delete maps',
        };
    }
}
