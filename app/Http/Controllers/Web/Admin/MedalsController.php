<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Medal;
use App\Models\PlayerMedal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MedalsController extends Controller
{
    public function index(Request $request)
    {
        /** @var \Illuminate\Pagination\LengthAwarePaginator */
        $medals = Medal::select([
            '*',
            DB::raw('COALESCE(pm.earned_count, 0) AS earned_count'),
        ])
            ->joinSub(
                'SELECT medal_id, COUNT(*) AS earned_count FROM player_medals GROUP BY medal_id',
                'pm', 'medals.id', '=', 'pm.medal_id', 'left'
            )
            ->indexFilterPaginate(perPage: 30, sortBy: 'title', order: 'asc');

        $medals->through(fn (Medal $medal) => $medal->makeVisible(['id']));

        if ($this->wantsInertia($request)) {
            return Inertia::render('Admin/Medals/Index', [
                'medals' => $medals,
            ]);
        } else {
            return $medals;
        }
    }

    public function medalsPlayerDoesntHave(Request $request, int $player)
    {
        return Medal::whereDoesntHave('earned', function ($q) use ($player) {
            $q->where('player_id', $player);
        })
            ->indexFilterPaginate(perPage: 30, sortBy: 'title', order: 'asc');
    }

    public function create()
    {
        return Inertia::render('Admin/Medals/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required',
            'description' => 'nullable|string',
            'hidden' => 'required|boolean',
            'image' => 'nullable|file|mimes:png',
        ]);

        $medal = new Medal;
        $medal->title = $data['title'];
        $medal->description = $data['description'];
        $medal->hidden = $data['hidden'];
        $medal->save();

        if ($data['image']) {
            $newMedal = Medal::find($medal->id);
            $request->file('image')->storeAs(
                'public/medals', $newMedal->uuid.'.png'
            );
        }

        return to_route('admin.medals.index');
    }

    public function edit(Medal $medal)
    {
        return Inertia::render('Admin/Medals/Edit', [
            'medal' => $medal->makeVisible(['id']),
        ]);
    }

    public function update(Request $request, Medal $medal)
    {
        $data = $request->validate([
            'title' => 'required',
            'description' => 'nullable|string',
            'hidden' => 'required|boolean',
            'image' => 'nullable|file|mimes:png',
            'clear_image' => 'nullable|boolean',
        ]);

        $medal->title = $data['title'];
        $medal->description = $data['description'];
        $medal->hidden = $data['hidden'];
        $medal->save();

        if ($data['image']) {
            $request->file('image')->storeAs(
                'public/medals', $medal->uuid.'.png'
            );
        } elseif ($data['clear_image'] === true) {
            Storage::delete('public/medals/'.$medal->uuid.'.png');
        }

        return to_route('admin.medals.index');
    }

    public function destroy(Medal $medal)
    {
        Storage::delete('public/medals/'.$medal->uuid.'.png');
        $medal->delete();

        return ['message' => 'Medal removed'];
    }

    public function addToPlayer(Request $request)
    {
        $data = $request->validate([
            'player_id' => 'required',
            'medal_uuid' => 'required',
        ]);

        $medal = Medal::where('uuid', $data['medal_uuid'])->firstOrFail();

        $medalAward = new PlayerMedal;
        $medalAward->player_id = $data['player_id'];
        $medalAward->medal_id = $medal->id;
        $medalAward->save();

        $medalAward->load('medal');

        return $medalAward;
    }

    public function removeFromPlayer(int $player, string $medal)
    {
        $medal = Medal::where('uuid', $medal)->firstOrFail();
        $medalAward = PlayerMedal::where('player_id', $player)
            ->where('medal_id', $medal->id)
            ->firstOrFail();

        $medalAward->delete();

        return ['message' => 'Removed medal award'];
    }
}
