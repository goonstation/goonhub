<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexQueryRequest;
use App\Http\Resources\JobBanResource;
use App\Models\GameAdmin;
use App\Models\JobBan;
use App\Rules\DateRange;
use App\Traits\ManagesJobBans;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @tags Job Bans
 */
class JobBansController extends Controller
{
    use ManagesJobBans;

    /**
     * List
     *
     * List filtered and paginated job bans
     *
     * @return AnonymousResourceCollection<LengthAwarePaginator<JobBanResource>>
     */
    public function index(IndexQueryRequest $request)
    {
        $request->validate([
            'filters.id' => 'int',
            /** @example main1 */
            'filters.server' => 'string',
            'filters.map' => 'string',
            'filters.game_type' => 'string',
            'filters.rp_mode' => 'boolean',
            'filters.crashed' => 'boolean',
            /**
             * A date or date range
             *
             * @example 2023/01/30 12:00:00 - 2023/02/01 12:00:00
             */
            'filters.ended_at' => new DateRange,
            /**
             * A date or date range
             *
             * @example 2023/01/30 12:00:00 - 2023/02/01 12:00:00
             */
            'filters.created_at' => new DateRange,
            /**
             * A date or date range
             *
             * @example 2023/01/30 12:00:00 - 2023/02/01 12:00:00
             */
            'filters.updated_at' => new DateRange,
        ]);

        return JobBanResource::collection(
            JobBan::with(['gameAdmin:id,ckey,name'])
                ->indexFilterPaginate()
        );
    }

    /**
     * Check
     *
     * Check if a job ban exists for given player and server details
     */
    public function check(Request $request)
    {
        $data = $this->validate($request, [
            'ckey' => 'required',
            'job' => 'required',
            'server_id' => 'nullable|string',
        ]);

        $serverId = isset($data['server_id']) ? $data['server_id'] : null;
        $jobBan = JobBan::getValidJobBans($data['ckey'], $data['job'], $serverId)->first();

        return new JobBanResource($jobBan);
    }

    /**
     * Get for player
     *
     * Get all job bans for a given player and server
     */
    public function getForPlayer(Request $request)
    {
        $data = $this->validate($request, [
            'ckey' => 'required',
            'server_id' => 'nullable|string',
        ]);

        $serverId = isset($data['server_id']) ? $data['server_id'] : null;
        $jobBans = JobBan::select('banned_from_job')
            ->where('ckey', $data['ckey'])
            ->where(function (Builder $builder) use ($serverId) {
                // Check if the ban applies to all servers, or the server id we were provided
                $builder->whereNull('server_id')
                    ->orWhere('server_id', $serverId);
            })
            ->where(function (Builder $builder) {
                // Check the ban is permanent, or has yet to expire
                $builder->whereNull('expires_at')
                    ->orWhere('expires_at', '>', Carbon::now()->toDateTimeString());
            })
            ->get()
            ->pluck('banned_from_job')
            ->unique();

        /**
         * A list of jobs this player is banned from
         *
         * @var array
         */
        return ['data' => $jobBans];
    }

    /**
     * Add
     *
     * Add a new job ban
     */
    public function store(Request $request)
    {
        try {
            return $this->addJobBan($request);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Update
     *
     * Update an existing job ban
     */
    public function update(Request $request, JobBan $jobBan)
    {
        try {
            return $this->updateJobBan($request, $jobBan);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Delete
     *
     * Delete an existing job ban
     */
    public function destroy(Request $request)
    {
        $data = $this->validate($request, [
            'game_admin_ckey' => 'required|exists:game_admins,ckey',
            'server_id' => 'nullable|string',
            'ckey' => 'required',
            'job' => 'required',
        ]);

        $gameAdmin = GameAdmin::where('ckey', $data['game_admin_ckey'])->first();

        $jobBans = JobBan::where('ckey', $data['ckey'])
            ->where('banned_from_job', $data['job']);

        if (isset($data['server_id'])) {
            $jobBans->where('server_id', $data['server_id']);
        }

        $jobBans = $jobBans->get();

        foreach ($jobBans as $jobBan) {
            $jobBan->deleted_by = $gameAdmin->id;
            $jobBan->save();
            $jobBan->delete();
        }

        return ['message' => 'Job bans removed'];
    }
}
