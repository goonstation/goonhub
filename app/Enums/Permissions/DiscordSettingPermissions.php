<?php

namespace App\Enums\Permissions;

enum DiscordSettingPermissions: string implements IsPermission
{
    use Permission;

    case VIEW = 'view-discord-settings';
    case UPDATE = 'update-discord-settings';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View Discord Settings',
            self::UPDATE => 'Update Discord Settings',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::VIEW => 'Can view discord settings',
            self::UPDATE => 'Can update discord settings',
        };
    }
}
