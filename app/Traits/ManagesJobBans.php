<?php

namespace App\Traits;

use App\Http\Requests\JobBans\StoreRequest;
use App\Http\Requests\JobBans\UpdateRequest;
use App\Http\Resources\JobBanResource;
use App\Models\JobBan;
use App\Services\CommonRequest;
use Exception;
use Illuminate\Support\Carbon;

trait ManagesJobBans
{
    private function addJobBan(StoreRequest $request)
    {
        $data = collect($request->validated());

        $serverId = $data->has('server_id') ? $data['server_id'] : null;

        // Check a ban doesn't already exist for the provided ckey and job
        $existingJobBan = JobBan::getValidJobBans(ckey($data['ckey']), $data['job'], $serverId)->first();
        if (! empty($existingJobBan)) {
            throw new Exception('The player is already banned from that job on this server.');
        }

        $expiresAt = null;
        if ($data->has('duration')) {
            $expiresAt = Carbon::now()->addSeconds($data['duration']);
        }

        $gameAdmin = $request->getGameAdmin();
        $gameServer = $request->getGameServer();
        $gameServerGroup = $request->getGameServerGroup();

        if (! $gameServer) {
            // If the ban isn't targeting a specific server, the user wants it to apply to all servers
            // So we get the originating server group and apply it to the ban
            $gameServerGroup = app(CommonRequest::class)->fromServerGroup();
        }

        $jobBan = new JobBan;
        $jobBan->round_id = $data->has('round_id') ? $data['round_id'] : null;
        $jobBan->ckey = ckey($data['ckey']);
        $jobBan->banned_from_job = $data['job'];
        $jobBan->reason = $data['reason'];
        $jobBan->expires_at = $expiresAt;
        $jobBan->gameAdmin()->associate($gameAdmin);
        if ($gameServer) {
            $jobBan->gameServer()->associate($gameServer);
        } elseif ($gameServerGroup) {
            $jobBan->gameServerGroup()->associate($gameServerGroup);
        }
        $jobBan->save();

        return new JobBanResource($jobBan);
    }

    private function updateJobBan(UpdateRequest $request, JobBan $jobBan)
    {
        $data = collect($request->validated());
        $serverId = $data->has('server_id') ? $data['server_id'] : null;

        // Check another ban doesn't already exist for the provided job
        /** @var JobBan|null */
        $existingJobBan = JobBan::getValidJobBans($jobBan->ckey, $data['job'], $serverId)->first();
        if ($existingJobBan && $jobBan->id !== $existingJobBan->id) {
            throw new Exception('The player is already banned from that job on this server.');
        }

        $newBan = $data->only(['reason']);
        $newBan['banned_from_job'] = $data['job'];

        // Ensure the server ID is nulled out if we're being told about it, and it's falsey
        if ($request->has('server_id')) {
            $newBan['server_id'] = $data['server_id'] ? $data['server_id'] : null;
        }

        // If the ban isn't targeting a specific server, the user wants it to apply to all servers
        // So we get the originating server group and apply it to the ban
        if ($newBan->has('server_id') && is_null($newBan['server_id'])) {
            $gameServerGroup = app(CommonRequest::class)->fromServerGroup();

            $newBan['server_group'] = $gameServerGroup->id;
        } else {
            $newBan['server_group'] = null;
        }

        if ($data->has('duration')) {
            // A falsey duration means it's essentially "unset", and thus now a permanent ban
            // Otherwise, the admin is altering how long the ban lasts
            if (! $data['duration']) {
                $newBan['expires_at'] = null;
            } else {
                // Ban is temporary, the duration starts from when the ban was first created
                $newExpiresAt = $jobBan->created_at->addSeconds($data['duration']);

                // Bans can't expire in the past
                if ($newExpiresAt->lte(Carbon::now())) {
                    throw new Exception('The ban cannot expire in the past, please increase the duration.');
                }

                $newBan['expires_at'] = $newExpiresAt->toDateTimeString();
            }
        }

        $jobBan->update($newBan->toArray());

        return new JobBanResource($jobBan);
    }
}
