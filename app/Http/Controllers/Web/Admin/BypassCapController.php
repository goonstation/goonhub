<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Traits\ManagesBypassCap;
use Illuminate\Http\Request;

class BypassCapController extends Controller
{
    use ManagesBypassCap;

    public function destroyMulti(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array',
        ]);

        $this->removeBypassCapsById($data['ids']);

        return ['message' => 'Players removed from cap bypass successfully'];
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
            $this->removeBypassCapByPlayer($player);

            return ['message' => 'Player removed from cap bypasses'];
        }

        $this->setBypassCapsByPlayer($player, $data['server_group_ids'], $data['server_ids']);
        $player->load(['bypassCap.serverGroups', 'bypassCap.servers']);

        return [
            'message' => 'Player added to cap bypasses successfully',
            'bypassCap' => $player->bypassCap,
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
            $this->removeBypassCapsByPlayerId($data['player_ids']);

            return ['message' => sprintf('%s removed from bypass cap', count($data['player_ids']) > 1 ? 'Players' : 'Player')];
        }

        $this->setBypassCapsByPlayerIds(
            $data['player_ids'],
            $request->input('server_group_ids', []),
            $request->input('server_ids', [])
        );

        return ['message' => sprintf('%s bypass cap updated', count($data['player_ids']) > 1 ? 'Players' : 'Player')];
    }
}
