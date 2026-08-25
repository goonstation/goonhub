<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\Permissions\WhitelistPermissions;
use App\Helpers\HasPermission;
use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Traits\ManagesWhitelist;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class WhitelistController extends Controller implements HasMiddleware
{
    use ManagesWhitelist;

    public static function middleware(): array
    {
        return [
            HasPermission::using(WhitelistPermissions::UPDATE, only: ['destroyMulti', 'toggle', 'bulkToggle']),
        ];
    }

    public function destroyMulti(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array',
        ]);

        $this->removeWhitelistsById($data['ids']);

        return ['message' => 'Whitelisted players removed successfully'];
    }

    public function toggle(Request $request, Player $player)
    {
        $data = $request->validate([
            'server_ids' => 'sometimes|array',
            'server_ids.*' => 'sometimes|integer|distinct|exists:game_servers,id',
            'server_group_ids' => 'sometimes|array',
            'server_group_ids.*' => 'sometimes|integer|distinct|exists:game_server_groups,id',
        ]);

        $removing = empty($data['server_ids']) && empty($data['server_group_ids']);

        if ($removing) {
            $this->removeWhitelistByPlayer($player);

            return ['message' => 'Player removed from whitelists'];
        }

        $this->setWhitelistsByPlayer($player, $data['server_group_ids'], $data['server_ids']);
        $player->load(['whitelist.serverGroups', 'whitelist.servers']);

        return [
            'message' => 'Player whitelisted successfully',
            'whitelist' => $player->whitelist,
        ];
    }

    public function bulkToggle(Request $request)
    {
        $data = $request->validate([
            'player_ids' => 'required|array|exists:players,id',
            'server_ids' => 'sometimes|array',
            'server_ids.*' => 'sometimes|integer|distinct|exists:game_servers,id',
            'server_group_ids' => 'sometimes|array',
            'server_group_ids.*' => 'sometimes|integer|distinct|exists:game_server_groups,id',
        ]);

        $removing = empty($data['server_ids']) && empty($data['server_group_ids']);

        if ($removing) {
            $this->removeWhitelistsByPlayerId($data['player_ids']);

            return ['message' => sprintf('%s removed from whitelists', count($data['player_ids']) > 1 ? 'Players' : 'Player')];
        }

        $this->setWhitelistsByPlayerIds(
            $data['player_ids'],
            $request->input('server_group_ids', []),
            $request->input('server_ids', [])
        );

        return ['message' => sprintf('%s whitelists updated', count($data['player_ids']) > 1 ? 'Players' : 'Player')];
    }
}
