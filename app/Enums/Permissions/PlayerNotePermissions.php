<?php

namespace App\Enums\Permissions;

enum PlayerNotePermissions: string implements IsPermission
{
    use Permission;

    case VIEW = 'view-player-notes';
    case ADD = 'add-player-notes';
    case UPDATE = 'update-player-notes';
    case DELETE = 'delete-player-notes';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View Player Notes',
            self::ADD => 'Add Player Notes',
            self::UPDATE => 'Update Player Notes',
            self::DELETE => 'Delete Player Notes',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::VIEW => 'Can view player notes',
            self::ADD => 'Can add player notes',
            self::UPDATE => 'Can update player notes',
            self::DELETE => 'Can delete player notes',
        };
    }
}
