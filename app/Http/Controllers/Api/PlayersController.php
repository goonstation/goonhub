<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlayerCompIdsResource;
use App\Http\Resources\PlayerIpsResource;
use App\Http\Resources\PlayerResource;
use App\Http\Resources\PlayerSearchResource;
use App\Http\Resources\PlayerStatsResource;
use App\Jobs\ImportByondMedalsForPlayer;
use App\Jobs\RecordByondJoinDate;
use App\Models\Player;
use App\Models\PlayerConnection;
use App\Traits\ManagesPlayers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlayersController extends Controller
{
    use ManagesPlayers;

    /**
     * Login
     *
     * Register a login for a player with associated details
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'ckey' => 'required|alpha_num',
            'key' => 'required|string',
            'ip' => 'required|ipv4',
            'comp_id' => 'required|integer',
            'byond_major' => 'nullable|integer',
            'byond_minor' => 'nullable|integer',
            'round_id' => 'nullable|integer|exists:game_rounds,id',
        ]);

        $player = Player::where('ckey', $data['ckey'])->first();
        $creatingPlayer = false;

        if (is_null($player)) {
            $creatingPlayer = true;
            $player = new Player;
            $player->ckey = $data['ckey'];
        }

        if (empty($player->key)) {
            $player->key = $data['key'];
        }

        $this->loginPlayer($player, $data);

        if (empty($player->byond_join_date)) {
            RecordByondJoinDate::dispatch($player->id, $player->ckey);
        }

        if ($creatingPlayer || ! $player->hasImportedMedals) {
            ImportByondMedalsForPlayer::dispatch($player->ckey);
        }

        return new PlayerResource($player);
    }

    /**
     * Search
     *
     * Search for a specific player
     *
     * One or multiple of either ckey, ip, or comp_id required
     */
    public function search(Request $request)
    {
        $data = $request->validate([
            'ckey' => 'required_without_all:ip,comp_id|nullable|alpha_num',
            'ip' => 'required_without_all:ckey,comp_id|nullable|ipv4',
            'comp_id' => 'required_without_all:ckey,ip|nullable|integer',
            /** When set, looks up player details by exact matches, rather than partial lookups */
            'exact' => 'nullable|boolean',
        ]);

        $exact = isset($data['exact']) && $data['exact'];

        $conns = collect();
        $playerConns = collect();
        $connFiltered = false;
        $conn = PlayerConnection::with('player:id,ckey')
            ->select('id', 'ip', 'comp_id', 'player_id', 'created_at');

        if (isset($data['ckey'])) {
            $ckey = $data['ckey'];
            $players = Player::with([
                'connections' => function ($query) {
                    $query->select('id', 'ip', 'comp_id', 'player_id', 'created_at')
                        ->distinct('ip', 'comp_id', 'player_id');
                },
            ]);

            if ($exact) {
                $players = $players->where('ckey', $ckey);
            } else {
                $players->where('ckey', 'LIKE', "%$ckey%");
            }
            $players = $players->get();

            // Create a shallower connections array, while also normalizing data by moving the ckey
            // field up to the root object for each connection
            foreach ($players as $player) {
                $pcs = $player->connections->toArray();
                foreach ($pcs as $key => $pc) {
                    $pcs[$key]['ckey'] = $player->ckey;
                }
                $playerConns = $playerConns->merge($pcs);
            }
        }
        if (isset($data['ip'])) {
            $ip = $data['ip'];
            if ($exact) {
                $conn = $conn->orWhere('ip', $ip);
            } else {
                $conn = $conn->orWhere('ip', 'LIKE', "%$ip%");
            }
            $connFiltered = true;
        }
        if (isset($data['comp_id'])) {
            $compId = $data['comp_id'];
            if ($exact) {
                $conn = $conn->orWhere('comp_id', $compId);
            } else {
                $conn = $conn->orWhere('comp_id', 'LIKE', "%$compId%");
            }
            $connFiltered = true;
        }

        if ($connFiltered) {
            $conn = $conn->distinct('ip', 'comp_id', 'player_id')->get();
            // Normalize data by moving ckey field up to the root object for each connection
            $conn = $conn->toArray();
            foreach ($conn as $key => $c) {
                $conn[$key]['ckey'] = $c['player']['ckey'];
                unset($conn[$key]['player']);
            }
            $conns = $conns->merge($conn);
        }
        if ($playerConns->isNotEmpty()) {
            $conns = $conns->merge($playerConns);
        }

        // If we have data from both player and player_connections, we might have duplicates
        // So we need this unique call in addition to the prior distinct calls
        $conns = $conns->unique(function (array $item) {
            return $item['ip'].$item['comp_id'].$item['ckey'];
        });

        $conns = $conns->sortByDesc('id');

        return PlayerSearchResource::collection(
            new PlayerSearchResource($conns)
        );
    }

    /**
     * Get IPs
     *
     * Get a list of IPs associated with a player ckey,
     * along with how many times they connected with each IP
     */
    public function getIps(Request $request)
    {
        $data = $request->validate([
            'ckey' => 'required|alpha_num',
        ]);

        $player = Player::with([
            'latestConnection',
            'connections' => function ($query) {
                $query->select(
                    DB::raw('DISTINCT(ip) as ip'),
                    DB::raw('COUNT(*) as connected'),
                    'player_id'
                )
                    ->groupBy('ip', 'player_id')
                    ->orderBy('connected', 'desc');
            },
        ])
            ->where('ckey', $data['ckey'])
            ->first();

        if (! $player) {
            return response()->json(['message' => 'Player does not exist'], 404);
        }

        $ips = $player->connections->map(function (PlayerConnection $item) {
            return ['ip' => $item['ip'], 'connected' => $item['connected']];
        });

        return new PlayerIpsResource([
            'latest_connection' => $player->latestConnection,
            'ips' => $ips,
        ]);
    }

    /**
     * Get Comp Ids
     *
     * Get a list of computed IDs associated with a player ckey,
     * along with how many times they connected with each computer ID
     */
    public function getCompIds(Request $request)
    {
        $data = $request->validate([
            'ckey' => 'required|alpha_num',
        ]);

        $player = Player::with([
            'latestConnection',
            'connections' => function ($query) {
                $query->select(
                    DB::raw('DISTINCT(comp_id) as comp_id'),
                    DB::raw('COUNT(*) as connected'),
                    'player_id'
                )
                    ->groupBy('comp_id', 'player_id')
                    ->orderBy('connected', 'desc');
            },
        ])
            ->where('ckey', $data['ckey'])
            ->first();

        if (! $player) {
            return response()->json(['message' => 'Player does not exist'], 404);
        }

        $compIds = $player->connections->map(function (PlayerConnection $item) {
            return ['comp_id' => $item['comp_id'], 'connected' => $item['connected']];
        });

        return new PlayerCompIdsResource([
            'latest_connection' => $player->latestConnection,
            'comp_ids' => $compIds,
        ]);
    }

    /**
     * Stats
     *
     * Get various statistics associated with a player
     */
    public function stats(Request $request)
    {
        $data = $request->validate([
            'ckey' => 'required|alpha_num',
        ]);

        $stats = Player::with('latestConnection')
            // Ok so I started out wanting to make this "eloquent-y" but this looks like hot garbage
            ->withCount([
                'participations as played' => function (Builder $query) {
                    $query->where(function (Builder $query) {
                        $query->where('round_id', null)->whereNot('legacy_data->rp_mode', true);
                    })
                        ->orWhere(function (Builder $query) {
                            $query->whereNot('round_id', null)->whereRelation('gameRound', 'rp_mode', '=', false);
                        });
                },
                'participations as played_rp' => function (Builder $query) {
                    $query->where(function (Builder $query) {
                        $query->where('round_id', null)->where('legacy_data->rp_mode', true);
                    })
                        ->orWhere(function (Builder $query) {
                            $query->whereNot('round_id', null)->whereRelation('gameRound', 'rp_mode', '=', true);
                        });
                },
                'connections as connected' => function (Builder $query) {
                    $query->where(function (Builder $query) {
                        $query->where('round_id', null)->whereNot('legacy_data->rp_mode', true);
                    })
                        ->orWhere(function (Builder $query) {
                            $query->whereNot('round_id', null)->whereRelation('gameRound', 'rp_mode', '=', false);
                        });
                },
                'connections as connected_rp' => function (Builder $query) {
                    $query->where(function (Builder $query) {
                        $query->where('round_id', null)->where('legacy_data->rp_mode', true);
                    })
                        ->orWhere(function (Builder $query) {
                            $query->whereNot('round_id', null)->whereRelation('gameRound', 'rp_mode', '=', true);
                        });
                },
            ])
            ->selectRaw(
                '(SELECT COALESCE(SUM(seconds_played), 0) FROM player_playtime where players.id = player_playtime.player_id) AS time_played'
            )
            ->where('ckey', $data['ckey'])
            ->firstOrFail();

        return new PlayerStatsResource($stats);
    }
}
