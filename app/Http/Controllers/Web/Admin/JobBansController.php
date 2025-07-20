<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobBan;
use App\Traits\ManagesJobBans;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class JobBansController extends Controller
{
    use ManagesJobBans;

    public function index(Request $request)
    {
        $jobBans = JobBan::with([
            'gameAdmin:id,name,ckey',
            'gameServer:id,server_id,short_name',
        ])->indexFilterPaginate(perPage: 30);

        if ($this->wantsInertia($request)) {
            return Inertia::render('Admin/JobBans/Index', [
                'jobBans' => $jobBans,
            ]);
        } else {
            return $jobBans;
        }
    }

    public function create()
    {
        return Inertia::render('Admin/JobBans/Create');
    }

    public function store(Request $request)
    {
        $request->merge([
            'game_admin_ckey' => $request->user()->gameAdmin->ckey,
        ]);
        if ($request->input('server_id') === 'all') {
            $request->merge(['server_id' => null]);
        }
        $jobBan = $this->addJobBan($request);
        $jobBan->load(['gameAdmin', 'gameServer']);

        if ($request->has('return_job_ban')) {
            return $jobBan;
        } else {
            return to_route('admin.job-bans.index');
        }
    }

    public function edit(JobBan $jobBan)
    {
        return Inertia::render('Admin/JobBans/Edit', [
            'jobBan' => $jobBan,
        ]);
    }

    public function update(Request $request, JobBan $jobBan)
    {
        try {
            $request = $request->merge([
                'game_admin_ckey' => $request->user()->gameAdmin->ckey,
            ]);
            if ($request->input('server_id') === 'all') {
                $request->merge(['server_id' => null]);
            }
            $this->updateJobBan($request, $jobBan);
        } catch (\Exception $e) {
            return Redirect::back()->withErrors(['error' => $e->getMessage()]);
        }

        return to_route('admin.job-bans.index');
    }

    public function show(int $jobBan)
    {
        $jobBan = JobBan::withTrashed()
            ->with([
                'gameAdmin:id,name,ckey',
                'gameServer',
                'deletedByGameAdmin',
            ])
            ->findOrFail($jobBan);

        return Inertia::render('Admin/JobBans/Show', [
            'jobBan' => $jobBan,
        ]);
    }

    public function destroy(Request $request, JobBan $jobBan)
    {
        $jobBan->deleted_by = $request->user()->gameAdmin->id;
        $jobBan->save();
        $jobBan->delete();

        return ['message' => 'Job ban removed'];
    }

    public function destroyMulti(Request $request)
    {
        $data = $this->validate($request, [
            'ids' => 'required|array',
        ]);

        $jobBans = JobBan::whereIn('id', $data['ids']);
        $jobBans->update(['deleted_by' => $request->user()->gameAdmin->id]);
        $jobBans->delete();

        return ['message' => 'Job bans removed'];
    }
}
