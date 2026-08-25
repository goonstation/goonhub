<?php

namespace App\Enums\Permissions;

enum OrchestrationPermissions: string implements IsPermission
{
    use Permission;

    case VIEW = 'view-orchestration-status';
    case RESTART = 'restart-orchestration-servers';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View Orchestration Status',
            self::RESTART => 'Restart Orchestrated Servers',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::VIEW => 'Can view orchestration status for servers',
            self::RESTART => 'Can restart game servers via orchestration',
        };
    }
}
