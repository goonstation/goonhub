<?php

namespace App\Http\Controllers\Web;

use App\Facades\GameBridge;
use App\Helpers\ChangelogHelper;
use App\Http\Controllers\Controller;
use App\Models\GameRound;
use App\Models\GameServer;
use App\Models\PlayersOnline;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Spatie\SchemaOrg\GamePlayMode;
use Spatie\SchemaOrg\Schema;

class HomeController extends Controller
{
    private function getPlayersOnline(array $serverIds)
    {
        // Get the sum of players online for each server, grouped by when we fetched them (usually 5 minute intervals)
        // Then, get the average of that total grouped by day
        $playersOnlineHistory = DB::table(function ($query) use ($serverIds) {
            $query
                ->select('created_at')
                ->selectRaw('Date(created_at) as date')
                ->selectRaw('sum(online) as online')
                ->whereIn('server_id', $serverIds)
                ->where('created_at', '>=', Carbon::today()->subDays(7))
                ->where('created_at', '<', Carbon::today())
                ->where('online', '!=', null)
                ->from('players_online')
                ->groupBy('created_at');
        }, 'grouped')
            ->selectRaw('grouped.date as date')
            ->selectRaw('avg(grouped.online) as online')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Just get the sum of players online right now
        $playersOnlineRightNow = PlayersOnline::selectRaw('Date(created_at) as date')
            ->selectRaw('sum(online) as online')
            ->whereIn('server_id', $serverIds)
            ->where('created_at', '>', Carbon::today())
            ->groupBy('created_at')
            ->orderBy('created_at', 'desc')
            ->limit(1)
            ->first();

        $playersOnline = $playersOnlineHistory->toArray();
        if ($playersOnlineRightNow) {
            $playersOnline[] = $playersOnlineRightNow->toArray();
        }

        return $playersOnline;
    }

    private function getLastRounds(array $serverIds)
    {
        return GameRound::with([
            'latestStationName:round_id,name',
        ])
            ->select([
                DB::raw('distinct on (server_id) server_id'),
                'id',
                'created_at',
                'ended_at',
            ])
            ->whereIn('server_id', $serverIds)
            ->whereNotNull('ended_at')
            ->orderByRaw('server_id, created_at DESC NULLS LAST')
            ->get();
    }

    private function getServerStatus(Request $request)
    {
        $server = $request->input('server');
        $res = GameBridge::server($server)->status();

        if ($res->failed()) {
            Inertia::share('errors', ['status' => $res->getMessage()]);

            return [];
        }

        return $res->getData();
    }

    public function index(Request $request)
    {
        $servers = GameServer::select('id', 'server_id', 'name')
            ->where('active', true)
            ->where('invisible', false)
            ->orderBy('server_id', 'asc')
            ->get()
            ->makeHidden('byond_link');
        $serverIds = $servers->pluck('server_id')->toArray();

        $this->setMeta(
            description: 'Dive into the world of Space Station 13\'s Goonstation branch with Goonhub. Get access to comprehensive statistics and stay up-to-date with the latest developments.',
        );
        $this->setHomeSchema();

        return Inertia::render('Home/Index', [
            'servers' => $servers,
            'playersOnline' => Inertia::defer(fn () => $this->getPlayersOnline($serverIds), 'playersOnline'),
            'lastRounds' => Inertia::defer(fn () => $this->getLastRounds($serverIds), 'lastRounds'),
            'changelog' => fn () => ChangelogHelper::get(7),
            'status' => Inertia::lazy(fn () => $this->getServerStatus($request)),
        ]);
    }

    private function setHomeSchema()
    {
        $author = Schema::organization()
            ->name('Goonstation')
            ->url('https://github.com/goonstation/goonstation');

        $playMode = Schema::gamePlayMode()
            ->name(GamePlayMode::MultiPlayer);

        $numberOfPlayers = Schema::quantitativeValue()
            ->value('Number')
            ->minValue(0)
            ->maxValue(200);

        $language = Schema::language()
            ->name('English')
            ->alternateName('en');

        $game = Schema::videoGame()
            ->name('Goonstation')
            ->description('')
            ->author($author)
            ->genre([''])
            ->operatingSystem('Windows')
            ->gamePlatform(['PC game'])
            ->playMode($playMode)
            ->numberOfPlayers($numberOfPlayers)
            ->downloadUrl('https://www.byond.com/download/')
            ->inLanguage($language);

        $this->setSchema($game);
    }

    public static function getOpenGraphData()
    {
        $servers = GameServer::select(['server_id', 'name', 'address', 'port'])
            ->where('active', true)
            ->where('invisible', false)
            ->orderBy('server_id', 'asc')
            ->get();

        $data = ['servers' => $servers->toArray()];
        foreach ($servers as $key => $server) {
            $res = GameBridge::server($server)->status();

            if ($res->failed()) {
                $data['servers'][$key]['error'] = true;

                continue;
            }

            $data['servers'][$key]['status'] = $res->getData();
        }

        return $data;
    }
}
