<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexQueryRequest;
use App\Http\Requests\JobBans\DestroyRequest;
use App\Http\Requests\JobBans\StoreRequest;
use App\Http\Requests\JobBans\UpdateRequest;
use App\Http\Resources\JobBanResource;
use App\Models\JobBan;
use App\Rules\DateRange;
use App\Services\CommonRequest;
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
            'filters.round' => 'int',
            'filters.game_admin' => 'string',
            /** @example main1 */
            'filters.server' => 'string',
            'filters.ckey' => 'string',
            'filters.banned_from_job' => 'string',
            'filters.reason' => 'string',
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
            JobBan::forApi()->with(['gameAdmin.player'])
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
        ]);

        $commonRequest = app(CommonRequest::class);
        $fromServerId = $commonRequest->fromServerId();
        $fromServerGroup = $commonRequest->fromServerGroup();

        $jobBan = JobBan::getValidJobBans(ckey($data['ckey']), $data['job'], $fromServerId, $fromServerGroup->id)->first();
        $jobBan->load('gameAdmin');

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
        ]);

        $commonRequest = app(CommonRequest::class);
        $fromServerId = $commonRequest->fromServerId();
        $fromServerGroup = $commonRequest->fromServerGroup();

        $jobBans = JobBan::select('banned_from_job')
            ->where('ckey', ckey($data['ckey']))
            ->where(function (Builder $query) use ($fromServerId, $fromServerGroup) {
                // Check if the ban applies to all servers, or the server id we were provided
                $query->whereNull(['server_id', 'server_group']);

                if ($fromServerId) {
                    $query->orWhere('server_id', $fromServerId);
                }

                if ($fromServerGroup) {
                    $query->orWhere('server_group', $fromServerGroup->id);
                }
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
    public function store(StoreRequest $request)
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
    public function update(UpdateRequest $request, JobBan $jobBan)
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
    public function destroy(DestroyRequest $request)
    {
        $data = $request->validated();
        $gameAdmin = $request->getGameAdmin();

        $jobBans = JobBan::where('ckey', ckey($data['ckey']))
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
