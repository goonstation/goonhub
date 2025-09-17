<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Whitelist\StoreWhitelistRequest;
use App\Models\PlayerWhitelist;
use App\Traits\ManagesWhitelist;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WhitelistController extends Controller
{
    use ManagesWhitelist;

    public function index(Request $request)
    {
        $whitelistedPlayers = PlayerWhitelist::with([
            'player:id,ckey,key',
            'servers',
        ])->indexFilterPaginate(perPage: 30);

        if ($this->wantsInertia($request)) {
            return Inertia::render('Admin/Whitelist/Index', [
                'whitelistedPlayers' => $whitelistedPlayers,
            ]);
        } else {
            return $whitelistedPlayers;
        }
    }

    public function create()
    {
        return Inertia::render('Admin/Whitelist/Create');
    }

    public function store(StoreWhitelistRequest $request)
    {
        $data = $request->validated();
        $this->setPlayersWhitelistServers($data['player_ids'], $data['server_ids']);

        return redirect()->route('admin.whitelist.index')
            ->with('success', count($data['player_ids']).' whitelisted player(s) added successfully');
    }

    public function edit(PlayerWhitelist $whitelistedPlayer)
    {
        $whitelistedPlayer->load('servers');

        return Inertia::render('Admin/Whitelist/Edit', [
            'whitelistedPlayer' => $whitelistedPlayer,
        ]);
    }

    public function update(Request $request, PlayerWhitelist $whitelistedPlayer)
    {
        $data = $request->validate([
            'server_ids' => 'required|array',
        ]);

        $this->updatePlayerWhitelistServers($whitelistedPlayer, $data['server_ids']);

        return to_route('admin.whitelist.index')
            ->with('success', 'Whitelisted player updated successfully');
    }

    public function destroy(PlayerWhitelist $whitelistedPlayer)
    {
        $this->removePlayerWhitelist($whitelistedPlayer);

        return ['message' => 'Whitelisted player removed successfully'];
    }

    public function destroyMulti(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array',
        ]);

        $this->removePlayerWhitelists($data['ids']);

        return ['message' => 'Whitelisted players removed successfully'];
    }

    public function bulkToggle(Request $request)
    {
        $data = $request->validate([
            'player_ids' => 'required|array|exists:players,id',
            'server_ids' => 'sometimes|array',
            'server_ids.*' => 'sometimes|integer|distinct|exists:game_servers,id',
        ]);

        $removing = empty($data['server_ids']);

        if ($removing) {
            PlayerWhitelist::whereIn('player_id', $data['player_ids'])->delete();

            return ['message' => 'Whitelisted players removed successfully'];
        }

        foreach ($data['player_ids'] as $playerId) {
            $whitelistedPlayer = PlayerWhitelist::firstOrCreate([
                'player_id' => $playerId,
            ]);

            $whitelistedPlayer->servers()->sync($data['server_ids']);
        }

        return ['message' => 'Whitelisted players updated successfully'];
    }
}
