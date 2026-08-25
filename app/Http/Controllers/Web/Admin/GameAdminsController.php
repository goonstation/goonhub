<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GameAdmins\IndexRequest;
use App\Models\PlayerAdmin;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GameAdminsController extends Controller
{
    public function index(IndexRequest $request)
    {
        return Inertia::render('Admin/GameAdmins/Index', [
            'gameAdmins' => Inertia::lazy(fn () => PlayerAdmin::with(['player', 'rank'])->indexFilterPaginate(perPage: 30)),
        ]);
    }

    public function show(Request $request, PlayerAdmin $gameAdmin)
    {
        return Inertia::render('Admin/GameAdmins/Show', [
            'gameAdmin' => $gameAdmin,
        ]);
    }
}
