<?php

namespace App\Traits;

use App\Http\Resources\JobBanResource;
use App\Models\GameAdmin;
use App\Models\JobBan;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

trait ManagesJobBans
{
    private function addJobBan(Request $request)
    {
        $data = $request->validate([
            'game_admin_ckey' => 'exists:game_admins,ckey',
            'round_id' => 'nullable|integer',
            'server_id' => 'nullable|string',
            'ckey' => 'required',
            'job' => 'required',
            'reason' => 'nullable|string',
            'duration' => 'nullable|integer',
        ]);

        $serverId = isset($data['server_id']) ? $data['server_id'] : null;

        // Check a ban doesn't already exist for the provided ckey and job
        $existingJobBan = JobBan::getValidJobBans(ckey($data['ckey']), $data['job'], $serverId)->first();
        if (! empty($existingJobBan)) {
            throw new Exception('The player is already banned from that job on this server.');
        }

        $expiresAt = null;
        if (isset($data['duration'])) {
            $expiresAt = Carbon::now()->addSeconds($data['duration']);
        }

        $gameAdmin = GameAdmin::where('ckey', ckey($data['game_admin_ckey']))->first();

        $jobBan = new JobBan;
        $jobBan->game_admin_id = $gameAdmin->id;
        $jobBan->round_id = isset($data['round_id']) ? $data['round_id'] : null;
        $jobBan->server_id = $serverId;
        $jobBan->ckey = ckey($data['ckey']);
        $jobBan->banned_from_job = $data['job'];
        $jobBan->reason = $data['reason'];
        $jobBan->expires_at = $expiresAt;
        $jobBan->save();

        return new JobBanResource($jobBan);
    }

    private function updateJobBan(Request $request, JobBan $jobBan)
    {
        $data = $request->validate([
            'server_id' => 'nullable|string',
            'job' => 'required',
            'reason' => 'nullable|string',
            'duration' => 'nullable|integer',
        ]);

        $serverId = isset($data['server_id']) ? $data['server_id'] : null;

        // Check another ban doesn't already exist for the provided job
        /** @var JobBan|null */
        $existingJobBan = JobBan::getValidJobBans($jobBan->ckey, $data['job'], $serverId)->first();
        if ($existingJobBan && $jobBan->id !== $existingJobBan->id) {
            throw new Exception('The player is already banned from that job on this server.');
        }

        $newBanDetails = $request->only(['server_id', 'reason']);
        $newBanDetails['banned_from_job'] = $data['job'];

        // Ensure the server ID is nulled out if we're being told about it, and it's falsey
        if (isset($data['server_id'])) {
            $newBanDetails['server_id'] = $serverId;
        }

        if (isset($data['duration'])) {
            // A falsey duration means it's essentially "unset", and thus now a permanent ban
            // Otherwise, the admin is altering how long the ban lasts
            if (! $data['duration']) {
                $newBanDetails['expires_at'] = null;
            } else {
                // Ban is temporary, the duration starts from when the ban was first created
                $newExpiresAt = $jobBan->created_at->addSeconds($data['duration']);

                // Bans can't expire in the past
                if ($newExpiresAt->lte(Carbon::now())) {
                    throw new Exception('The ban cannot expire in the past, please increase the duration.');
                }

                $newBanDetails['expires_at'] = $newExpiresAt->toDateTimeString();
            }
        }

        $jobBan->update($newBanDetails);

        return new JobBanResource($jobBan);
    }
}
