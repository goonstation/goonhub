<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mentors\StoreMentorRequest;
use App\Models\PlayerMentor;
use App\Traits\IndexableQuery;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MentorsController extends Controller
{
    use IndexableQuery;

    public function index(Request $request)
    {
        $mentors = $this->indexQuery(
            PlayerMentor::with([
                'player:id,ckey,key',
            ]),
            perPage: 30);

        if ($this->wantsInertia($request)) {
            return Inertia::render('Admin/Mentors/Index', [
                'mentors' => $mentors,
            ]);
        } else {
            return $mentors;
        }
    }

    public function create()
    {
        return Inertia::render('Admin/Mentors/Create');
    }

    public function store(StoreMentorRequest $request)
    {
        $data = $request->validated();

        foreach ($data['player_ids'] as $playerId) {
            PlayerMentor::firstOrCreate([
                'player_id' => $playerId,
            ]);
        }

        return redirect()->route('admin.mentors.index')
            ->with('success', count($data['player_ids']).' mentor(s) added successfully');
    }

    public function destroy(PlayerMentor $mentor)
    {
        $mentor->delete();

        return ['message' => 'Mentor removed successfully'];
    }

    public function destroyMulti(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array',
        ]);

        PlayerMentor::whereIn('id', $data['ids'])->delete();

        return ['message' => 'Mentors removed successfully'];
    }

    public function bulkToggle(Request $request)
    {
        $data = $request->validate([
            'player_ids' => 'required|array|exists:players,id',
            'make_mentor' => 'required|boolean',
        ]);

        if ($data['make_mentor']) {
            $existingMentors = PlayerMentor::whereIn('player_id', $data['player_ids'])->get();
            $nonMentors = collect($data['player_ids'])->diff($existingMentors->pluck('player_id'));
            PlayerMentor::insert(
                $nonMentors->map(fn ($id) => ['player_id' => $id, 'created_at' => now(), 'updated_at' => now()])
                    ->toArray()
            );
        } else {
            PlayerMentor::whereIn('player_id', $data['player_ids'])->delete();
        }

        return ['message' => 'Mentors updated successfully'];
    }
}
