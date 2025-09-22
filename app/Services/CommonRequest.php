<?php

namespace App\Services;

use App\Models\GameServer;
use App\Models\PlayerAdmin;
use Illuminate\Support\Facades\Cache;

class CommonRequest
{
    const FROM_SERVER_HEADER_NAME = 'X-Server-Id';

    const TARGET_SERVER_INPUT_NAME = 'server_id';

    const TARGET_GAME_ADMIN_ID_INPUT_NAME = 'game_admin_id';

    const TARGET_GAME_ADMIN_CKEY_INPUT_NAME = 'game_admin_ckey';

    public function fromServerId()
    {
        return request()->header(self::FROM_SERVER_HEADER_NAME);
    }

    public function fromServer()
    {
        $serverId = $this->fromServerId();
        if (! $serverId) {
            return null;
        }

        return Cache::memo('array')->rememberForever('request_from_server', function () use ($serverId) {
            return GameServer::firstWhere('server_id', $serverId);
        });
    }

    public function fromServerGroup()
    {
        $server = $this->fromServer();
        if (! $server) {
            return null;
        }

        return Cache::memo('array')->rememberForever('request_from_server_group', function () use ($server) {
            return $server->group;
        });
    }

    public function targetServerId()
    {
        $serverId = request()->input(self::TARGET_SERVER_INPUT_NAME);

        return $serverId === 'all' ? null : $serverId;
    }

    public function targetServer()
    {
        $serverId = $this->targetServerId();
        if (! $serverId) {
            return null;
        }

        return Cache::memo('array')->rememberForever('request_target_server', function () use ($serverId) {
            if (is_numeric($serverId)) {
                return GameServer::find($serverId);
            }

            return GameServer::firstWhere('server_id', $serverId);
        });
    }

    public function targetServerGroup()
    {
        $server = $this->targetServer();
        if (! $server) {
            return null;
        }

        return Cache::memo('array')->rememberForever('request_target_server_group', function () use ($server) {
            return $server->group;
        });
    }

    public function targetingGameAdmin()
    {
        $request = request();

        return $request->filled(self::TARGET_GAME_ADMIN_ID_INPUT_NAME) || $request->filled(self::TARGET_GAME_ADMIN_CKEY_INPUT_NAME);
    }

    public function targetGameAdmin()
    {
        if (! $this->targetingGameAdmin()) {
            return null;
        }

        return Cache::memo('array')->rememberForever('request_target_game_admin', function () {
            $request = request();
            $gameAdminId = $request->input(self::TARGET_GAME_ADMIN_ID_INPUT_NAME);
            $gameAdminCkey = $request->input(self::TARGET_GAME_ADMIN_CKEY_INPUT_NAME);
            $gameAdmin = null;

            if ($gameAdminId) {
                $gameAdmin = PlayerAdmin::find($gameAdminId);
            }

            if ($gameAdminCkey && ! $gameAdmin) {
                $gameAdmin = PlayerAdmin::whereRelation('player', 'ckey', $gameAdminCkey)->first();
            }

            return $gameAdmin;
        });
    }
}
