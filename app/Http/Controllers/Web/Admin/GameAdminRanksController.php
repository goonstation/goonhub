<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\Permissions\GameAdminRankPermissions;
use App\Helpers\HasPermission;
use App\Http\Controllers\Controller;
use App\Models\GameAdminRank;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Inertia\Inertia;

class GameAdminRanksController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            HasPermission::using(GameAdminRankPermissions::VIEW, only: ['index']),
            HasPermission::using(GameAdminRankPermissions::ADD, only: ['create', 'store']),
        ];
    }

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
