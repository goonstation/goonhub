<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hos\StoreHosRequest;
use App\Models\PlayerHos;
use App\Traits\IndexableQuery;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HosController extends Controller
{
    use IndexableQuery;

    public function index(Request $request)
    {
        $hos = $this->indexQuery(
            PlayerHos::with([
                'player:id,ckey,key',
            ]),
            perPage: 30);

        if ($this->wantsInertia($request)) {
            return Inertia::render('Admin/Hos/Index', [
                'hos' => $hos,
            ]);
        } else {
            return $hos;
        }
    }

    public function create()
    {
        return Inertia::render('Admin/Hos/Create');
    }

    public function store(StoreHosRequest $request)
    {
        $data = $request->validated();

        foreach ($data['player_ids'] as $player_id) {
            PlayerHos::create([
                'player_id' => $player_id,
            ]);
        }

        return redirect()->route('admin.hos.index')
            ->with('success', count($data['player_ids']).' Head(s) of Staff added successfully');
    }

    public function destroy(PlayerHos $hos)
    {
        $hos->delete();

        return ['message' => 'Head of Staff removed successfully'];
    }

    public function destroyMulti(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array',
        ]);

        PlayerHos::whereIn('id', $data['ids'])->delete();

        return ['message' => 'Heads of Staff removed successfully'];
    }
}
