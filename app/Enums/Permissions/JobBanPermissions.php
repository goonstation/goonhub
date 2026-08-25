<?php

namespace App\Enums\Permissions;

enum JobBanPermissions: string implements IsPermission
{
    use Permission;

    case VIEW = 'view-job-bans';
    case ADD = 'add-job-bans';
    case UPDATE = 'update-job-bans';
    case DELETE = 'delete-job-bans';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View Job Bans',
            self::ADD => 'Add Job Bans',
            self::UPDATE => 'Update Job Bans',
            self::DELETE => 'Delete Job Bans',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::VIEW => 'Can view and check job bans',
            self::ADD => 'Can add job bans',
            self::UPDATE => 'Can update job bans',
            self::DELETE => 'Can delete job bans',
        };
    }
}
