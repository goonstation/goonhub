<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GameAdminRanks\IndexRequest;
use App\Models\GameAdminRank;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GameAdminRanksController extends Controller
{
    public function index(IndexRequest $request)
    {
        return Inertia::render('Admin/GameAdminRanks/Index', [
            'gameAdminRanks' => Inertia::lazy(fn () => GameAdminRank::withCount('admins')->indexFilterPaginate(perPage: 30)),
        ]);
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
