<?php

namespace App\Traits;

use App\Http\Requests\GameBuildCancelRequest;
use App\Http\Requests\GameBuildCreateRequest;
use App\Http\Resources\GameBuildStatusCurrentResource;
use App\Http\Resources\GameBuildStatusQueuedResource;
use App\Jobs\GameBuild as GameBuildJob;
use App\Libraries\GameBuilder\Build as GameBuildBuild;
use App\Models\GameAdmin;
use App\Models\GameBuild;
use App\Models\GameBuildSetting;
use App\Models\GameServer;
use App\Models\MapSwitch;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

trait ManagesGameBuilds
{
    private function getStatus()
    {
        $res = ['current' => [], 'queued' => []];
        /** @var \Illuminate\Redis\Connections\PhpRedisConnection */
        $redis = Redis::client();
        $reservedJobs = $redis->zrange('queues:default:reserved', 0, -1);
        foreach ($reservedJobs as $job) {
            $job = json_decode($job);
            if ($job->data->commandName === GameBuildJob::class) {
                $data = unserialize($job->data->command);
                /** @var GameServer */
                $server = $data->server;
                $build = GameBuild::firstWhere('id', Cache::get("GameBuild-{$server->server_id}-build"));
                $runningJob = collect([
                    'admin' => $data->admin,
                    'server' => $server,
                    'build' => $build,
                    'mapSwitch' => $data->mapSwitch,
                    'startedAt' => Carbon::createFromTimestamp($job->pushedAt),
                    'cancelled' => false,
                ]);

                if (Cache::has("GameBuild-{$server->server_id}-cancel")) {
                    $runningJob->put('cancelled', true);
                }

                $queuedJob = Cache::get("GameBuild-{$server->server_id}-queued");
                if ($queuedJob) {
                    $res['queued'][] = new GameBuildStatusQueuedResource([
                        'admin' => $queuedJob['admin'],
                        'server' => $server,
                        'mapSwitch' => $queuedJob['mapSwitch'],
                        'startedAt' => $queuedJob['pushedAt'],
                    ]);
                }

                $res['current'][] = new GameBuildStatusCurrentResource($runningJob);
            }
        }

        return $res;
    }

    private function addBuild(GameBuildCreateRequest $request)
    {
        $admin = GameAdmin::where('ckey', $request['game_admin_ckey'])->firstOrFail();
        $server = GameServer::where('server_id', $request['server_id'])->firstOrFail();
        $setting = GameBuildSetting::where('server_id', $request['server_id'])->firstOrFail();

        $switchMap = false;
        if (! empty($request['map'])) {
            $switchMap = true;
            $setting->map_id = $request['map'];
            $setting->save();
        } else {
            if (GameBuildJob::isBuilding($request['server_id'])) {
                throw new \Exception('Already building that server');
            }
        }

        if (! empty($request['round_id']) && ! empty($request['votes'])) {
            $mapSwitch = new MapSwitch;
            $mapSwitch->game_admin_id = $admin->id;
            $mapSwitch->round_id = $request['round_id'];
            $mapSwitch->server_id = $request['server_id'];
            $mapSwitch->map = $request['map'];
            $mapSwitch->votes = $request['votes'];
            $mapSwitch->save();
        }

        GameBuildJob::dispatch($admin, $server, $switchMap);
    }

    private function cancelBuild(GameBuildCancelRequest $request)
    {
        $type = $request->input('type', 'current');
        $admin = GameAdmin::where('ckey', $request['game_admin_ckey'])->firstOrFail();

        if ($type === 'current') {
            if (! GameBuildJob::isBuilding($request['server_id'])) {
                return abort(404, 'No build in process');
            }

            GameBuildBuild::cancel($request['server_id'], $admin->id);
        } elseif ($type === 'queued') {
            if (! GameBuildJob::isQueued($request['server_id'])) {
                return abort(404, 'No build queued');
            }

            GameBuildJob::cancelQueuedBuild($request['server_id'], $admin->id);
        }
    }
}
