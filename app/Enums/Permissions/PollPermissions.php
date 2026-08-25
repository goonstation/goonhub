<?php

namespace App\Enums\Permissions;

enum PollPermissions: string implements IsPermission
{
    use Permission;

    case VIEW = 'view-polls';
    case ADD = 'add-polls';
    case UPDATE = 'update-polls';
    case DELETE = 'delete-polls';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View Polls',
            self::ADD => 'Add Polls',
            self::UPDATE => 'Update Polls',
            self::DELETE => 'Delete Polls',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::VIEW => 'Can list and fetch polls',
            self::ADD => 'Can add polls and poll options',
            self::UPDATE => 'Can update polls and poll options',
            self::DELETE => 'Can delete polls and poll options',
        };
    }
}
