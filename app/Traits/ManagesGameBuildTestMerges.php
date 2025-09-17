<?php

namespace App\Traits;

use App\Http\Requests\GameBuildTestMergeCreateRequest;
use App\Http\Requests\GameBuildTestMergeUpdateRequest;
use App\Http\Resources\GameBuildTestMergeResource;
use App\Libraries\DiscordBot;
use App\Models\GameBuildSetting;
use App\Models\GameBuildTestMerge;
use App\Models\GameServer;
use GrahamCampbell\GitHub\Facades\GitHub;

trait ManagesGameBuildTestMerges
{
    private const TM_LABEL = 'S-Testmerged';

    private function getPullRequestLabels(int $prId)
    {
        $labels = [];

        try {
            /** @var \Github\Client */
            $conn = GitHub::connection();
            $labels = $conn->issue()->labels()->all(
                config('goonhub.github_organization'),
                config('goonhub.github_repo'),
                $prId
            );
        } catch (\Throwable) {
            //
        }

        return collect($labels);
    }

    private function addPullRequestLabel(int $prId)
    {
        try {
            /** @var \Github\Client */
            $conn = GitHub::connection();
            $conn->issue()->labels()->add(
                config('goonhub.github_organization'),
                config('goonhub.github_repo'),
                $prId,
                self::TM_LABEL
            );
        } catch (\Throwable) {
            //
        }
    }

    private function removePullRequestLabel(int $prId)
    {
        try {
            /** @var \Github\Client */
            $conn = GitHub::connection();
            $conn->issue()->labels()->remove(
                config('goonhub.github_organization'),
                config('goonhub.github_repo'),
                $prId,
                self::TM_LABEL
            );
        } catch (\Throwable) {
            //
        }
    }

    private function announceOnDiscord(string $type, array $servers, int $prId, ?string $commit = null)
    {
        $gameServers = GameServer::select(['short_name'])->whereIn('server_id', $servers)->get();
        try {
            DiscordBot::export("testmerges/$type", 'POST', [
                'pr' => $prId,
                'servers' => $gameServers->pluck('short_name')->toArray(),
                'commit' => $commit,
            ]);
        } catch (\Throwable) {
            // ignore
        }
    }

    private function addTestMerge(GameBuildTestMergeCreateRequest $request)
    {
        $gameAdmin = $request->getGameAdmin();
        $servers = $request->input('server_ids', [$request->input('server_id')]);
        $buildSettings = GameBuildSetting::whereIn('server_id', $servers)->get();
        if ($buildSettings->isEmpty()) {
            if (count($servers) === 1) {
                throw new \Exception("No build settings exist for server {$servers[0]}.");
            } else {
                throw new \Exception('No build settings exist for those servers.');
            }
        }

        foreach ($servers as $server) {
            $setting = $buildSettings->firstWhere('server_id', $server);
            if (! $setting) {
                throw new \Exception("No build settings exist for server $server.");
            }

            $exists = GameBuildTestMerge::where('pr_id', $request['pr_id'])->where('setting_id', $setting->id)->exists();
            if ($exists) {
                throw new \Exception("A test merge with that PR ID already exists for server $server.");
            }
        }

        $testMerges = collect();
        foreach ($buildSettings as $setting) {
            $testMerge = new GameBuildTestMerge;
            $testMerge->pr_id = $request['pr_id'];
            $testMerge->commit = isset($request['commit']) ? $request['commit'] : null;
            $testMerge->buildSettings()->associate($setting);
            $testMerge->addedBy()->associate($gameAdmin);
            $testMerge->save();
            $testMerges->add($testMerge);
        }

        $labels = $this->getPullRequestLabels($request['pr_id']);
        if ($labels->doesntContain('name', self::TM_LABEL)) {
            $this->addPullRequestLabel($request['pr_id']);
        }

        $this->announceOnDiscord('added', $servers, $request['pr_id'], isset($request['commit']) ?: $request['commit']);

        return GameBuildTestMergeResource::collection($testMerges);
    }

    private function updateTestMerge(GameBuildTestMergeUpdateRequest $request, GameBuildTestMerge $testMerge)
    {
        if ($request->has('pr_id') || $request->has('server_id')) {
            $prId = $request->input('pr_id', $testMerge->pr_id);

            $settingId = $testMerge->setting_id;

            if ($request->has('server_id')) {
                $newBuildSetting = GameBuildSetting::firstWhere('server_id', $request['server_id']);
                if (! $newBuildSetting) {
                    throw new \Exception('No build settings exist for that server.');
                }
                $settingId = $newBuildSetting->id;
                $request = $request->merge(['setting_id' => $settingId]);
            }

            $exists = GameBuildTestMerge::whereNot('id', $testMerge->id)
                ->where('pr_id', $prId)
                ->where('setting_id', $settingId)
                ->exists();

            if ($exists) {
                throw new \Exception('A test merge with that PR ID already exists for that server.');
            }
        }

        foreach ($request->all() as $key => $val) {
            if ($key === 'game_admin_id' || $key === 'game_admin_ckey' || $key === 'server_id') {
                continue;
            }
            $testMerge[$key] = $val;
        }

        $gameAdmin = $request->getGameAdmin();
        if ($request->missing('commit')) {
            $testMerge->commit = null;
        }
        $testMerge->updatedBy()->associate($gameAdmin);
        $testMerge->save();

        $this->announceOnDiscord('updated', [$testMerge->buildSettings->server_id], $testMerge->pr_id, $testMerge->commit);

        return new GameBuildTestMergeResource($testMerge);
    }

    private function destroyTestMerge(GameBuildTestMerge $testMerge)
    {
        $otherTestMerges = GameBuildTestMerge::where('pr_id', $testMerge->pr_id)
            ->whereNot('id', $testMerge->id)
            ->exists();

        if (! $otherTestMerges) {
            $labels = $this->getPullRequestLabels($testMerge->pr_id);
            if ($labels->contains('name', self::TM_LABEL)) {
                $this->removePullRequestLabel($testMerge->pr_id);
            }
        }

        $this->announceOnDiscord('removed', [$testMerge->buildSettings->server_id], $testMerge->pr_id);

        $testMerge->delete();
    }
}
