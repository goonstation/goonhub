<?php

namespace App\Enums\Permissions;

enum GameBuildArtifactPermissions: string implements IsPermission
{
    use Permission;

    case CHECK = 'check-game-build-artifacts';
    case DOWNLOAD = 'download-game-build-artifacts';

    public function label(): string
    {
        return match ($this) {
            self::CHECK => 'Check Game Build Artifacts',
            self::DOWNLOAD => 'Download Game Build Artifacts',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::CHECK => 'Can check latest build artifact version',
            self::DOWNLOAD => 'Can download game, BYOND, and Rust-G artifacts',
        };
    }
}
