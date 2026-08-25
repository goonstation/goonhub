<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\Permissions\GameAdminPermissions;
use App\Helpers\HasPermission;
use App\Http\Controllers\Controller;
use App\Models\PlayerAdmin;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Inertia\Inertia;

class GameAdminsController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            HasPermission::using(GameAdminPermissions::VIEW, only: ['index', 'show']),
        ];
    }

    public function index(Request $request)
    {
        $gameAdmins = PlayerAdmin::with(['player', 'rank'])->indexFilterPaginate(perPage: 30);

        if ($this->wantsInertia($request)) {
            return Inertia::render('Admin/GameAdmins/Index', [
                'gameAdmins' => $gameAdmins,
            ]);
        } else {
            return $gameAdmins;
        }
    }

    public function show(Request $request, PlayerAdmin $gameAdmin)
    {
        return Inertia::render('Admin/GameAdmins/Show', [
            'gameAdmin' => $gameAdmin,
        ]);
    }
}
