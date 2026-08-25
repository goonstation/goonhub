<?php

namespace App\Enums;

enum Roles: string
{
    case SUPER_ADMIN = 'Super Admin';
    case GAME_SERVER = 'Game Server';
    case SITE_ADMIN = 'Site Admin';
    case GAME_ADMIN = 'Game Admin';
    case TEST = 'Test';

    public function description(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::GAME_SERVER => 'Game Server',
            self::SITE_ADMIN => 'Site Admin',
            self::GAME_ADMIN => 'Game Admin',
            self::TEST => 'Test',
        };
    }
}
