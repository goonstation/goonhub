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
            ->with('success', count($data['player_ids']).' Head(s) of Security added successfully');
    }

    public function destroy(PlayerHos $hos)
    {
        $hos->delete();

        return ['message' => 'Head of Security removed successfully'];
    }

    public function destroyMulti(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array',
        ]);

        PlayerHos::whereIn('id', $data['ids'])->delete();

        return ['message' => 'Heads of Security removed successfully'];
    }

    public function bulkToggle(Request $request)
    {
        $data = $request->validate([
            'player_ids' => 'required|array|exists:players,id',
            'make_hos' => 'required|boolean',
        ]);

        if ($data['make_hos']) {
            $existingHos = PlayerHos::whereIn('player_id', $data['player_ids'])->get();
            $nonHos = collect($data['player_ids'])->diff($existingHos->pluck('player_id'));
            PlayerHos::insert(
                $nonHos->map(fn ($id) => ['player_id' => $id, 'created_at' => now(), 'updated_at' => now()])
                    ->toArray()
            );
        } else {
            PlayerHos::whereIn('player_id', $data['player_ids'])->delete();
        }

        return ['message' => 'Heads of Security updated successfully'];
    }
}
