<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Whitelist\StoreWhitelistRequest;
use App\Models\PlayerWhitelist;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WhitelistController extends Controller
{
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

        foreach ($data['player_ids'] as $playerId) {
            $whitelistedPlayer = PlayerWhitelist::firstOrCreate([
                'player_id' => $playerId,
            ]);

            $whitelistedPlayer->servers()->sync($data['server_ids']);
        }

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

        $whitelistedPlayer->servers()->sync($data['server_ids']);

        return to_route('admin.whitelist.index')
            ->with('success', 'Whitelisted player updated successfully');
    }

    public function destroy(PlayerWhitelist $whitelistedPlayer)
    {
        $whitelistedPlayer->delete();

        return ['message' => 'Whitelisted player removed successfully'];
    }

    public function destroyMulti(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array',
        ]);

        PlayerWhitelist::whereIn('id', $data['ids'])->delete();

        return ['message' => 'Whitelisted players removed successfully'];
    }
}
