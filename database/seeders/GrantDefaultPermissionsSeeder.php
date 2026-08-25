<?php

namespace Database\Seeders;

use App\Enums\Roles;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class GrantDefaultPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guardName = 'web';

        $gameAdminPermissions = [
            ...\App\Enums\Permissions\BanPermissions::values(),
            ...\App\Enums\Permissions\BypassCapPermissions::values(),
            ...\App\Enums\Permissions\DectalkPermissions::values(),
            ...\App\Enums\Permissions\ErrorPermissions::values(),
            ...\App\Enums\Permissions\EventPermissions::values(),
            ...\App\Enums\Permissions\GameAdminPermissions::values(),
            ...\App\Enums\Permissions\GameBuildArtifactPermissions::values(),
            ...\App\Enums\Permissions\GameBuildPermissions::values(),
            ...\App\Enums\Permissions\GameBuildSettingPermissions::values(),
            ...\App\Enums\Permissions\GameBuildTestMergePermissions::values(),
            \App\Enums\Permissions\GameRoundPermissions::VIEW->value,
            ...\App\Enums\Permissions\GauntletPermissions::values(),
            ...\App\Enums\Permissions\HosPermissions::values(),
            ...\App\Enums\Permissions\JobBanPermissions::values(),
            ...\App\Enums\Permissions\LogPermissions::values(),
            ...\App\Enums\Permissions\MapPermissions::values(),
            ...\App\Enums\Permissions\MedalPermissions::values(),
            ...\App\Enums\Permissions\MentorPermissions::values(),
            ...\App\Enums\Permissions\OrchestrationPermissions::values(),
            ...\App\Enums\Permissions\PlayerAntagPermissions::values(),
            ...\App\Enums\Permissions\PlayerMedalPermissions::values(),
            ...\App\Enums\Permissions\PlayerMetadataPermissions::values(),
            ...\App\Enums\Permissions\PlayerNotePermissions::values(),
            ...\App\Enums\Permissions\PlayerPermissions::values(),
            ...\App\Enums\Permissions\PlayerSavePermissions::values(),
            ...\App\Enums\Permissions\PollPermissions::values(),
            ...\App\Enums\Permissions\RemoteMusicPermissions::values(),
            ...\App\Enums\Permissions\VpnCheckPermissions::values(),
            ...\App\Enums\Permissions\VpnWhitelistPermissions::values(),
            ...\App\Enums\Permissions\WhitelistPermissions::values(),
        ];

        $gameServerPermissions = [
            ...$gameAdminPermissions,
            ...\App\Enums\Permissions\GameAuthPermissions::values(),
            ...\App\Enums\Permissions\GameBuildArtifactPermissions::values(),
            ...\App\Enums\Permissions\GameRoundPermissions::values(),
            ...\App\Enums\Permissions\NumbersStationPermissions::values(),
            ...\App\Enums\Permissions\PlayerParticipationPermissions::values(),
            ...\App\Enums\Permissions\PlayerPlaytimePermissions::values(),
            ...\App\Enums\Permissions\RandomEntryPermissions::values(),
            ...\App\Enums\Permissions\ServerPerformancePermissions::values(),
            \App\Enums\Permissions\UserPermissions::LINK_DISCORD->value,
            \App\Enums\Permissions\UserPermissions::UNLINK_DISCORD->value,
        ];

        $siteAdminPermissions = [
            ...$gameAdminPermissions,
            ...\App\Enums\Permissions\AuditPermissions::values(),
            ...\App\Enums\Permissions\DiscordSettingPermissions::values(),
            ...\App\Enums\Permissions\GameAdminRankPermissions::values(),
            ...\App\Enums\Permissions\RedirectPermissions::values(),
            ...\App\Enums\Permissions\TestPermissions::values(),
            ...\App\Enums\Permissions\UserPermissions::values(),
        ];

        $gameAdminRole = Role::findByName(Roles::GAME_ADMIN->value, $guardName);
        $gameAdminRole->givePermissionTo($gameAdminPermissions);

        $gameServerRole = Role::findByName(Roles::GAME_SERVER->value, $guardName);
        $gameServerRole->givePermissionTo($gameServerPermissions);

        $siteAdminRole = Role::findByName(Roles::SITE_ADMIN->value, $guardName);
        $siteAdminRole->givePermissionTo($siteAdminPermissions);
    }
}
