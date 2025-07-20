<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameAdminRank;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GameAdminRanksController extends Controller
{
    public function index(Request $request)
    {
        $gameAdminRanks = GameAdminRank::withCount('admins')->indexFilterPaginate(perPage: 30);

        if ($this->wantsInertia($request)) {
            return Inertia::render('Admin/GameAdminRanks/Index', [
                'gameAdminRanks' => $gameAdminRanks,
            ]);
        } else {
            return $gameAdminRanks;
        }
    }

    public function create(Request $request)
    {
        return Inertia::render('Admin/GameAdminRanks/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'rank' => 'required|string',
        ]);

        $gameAdminRank = new GameAdminRank;
        $gameAdminRank->rank = $data['rank'];
        $gameAdminRank->save();

        return to_route('admin.game-admin-ranks.index');
    }
}
