<?php

namespace App\Enums\Permissions;

enum AuditPermissions: string implements IsPermission
{
    use Permission;

    case VIEW = 'view-audits';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View Audits',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::VIEW => 'Can view audits',
        };
    }
}
