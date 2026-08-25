<?php

namespace App\Enums\Permissions;

enum NumbersStationPermissions: string implements IsPermission
{
    use Permission;

    case VIEW = 'view-numbers-station';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View Numbers Station',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::VIEW => 'Can view numbers station password',
        };
    }
}
