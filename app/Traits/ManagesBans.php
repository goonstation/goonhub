<?php

namespace App\Traits;

use App\Http\Requests\Bans\StoreRequest;
use App\Http\Resources\BanResource;
use App\Models\Ban;
use App\Models\BanDetail;
use App\Models\Player;
use App\Models\PlayerNote;
use App\Services\CommonRequest;
use Carbon\CarbonInterval;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

trait ManagesBans
{
    private function banHistory(string $ckey, Collection $compIds, Collection $ips)
    {
        $bans = Ban::with([
            'gameAdmin',
            'gameServer',
            'originalBanDetail',
            'details',
            'gameRound',
        ])
            ->withTrashed()
            ->whereHas('details', function ($query) use ($ckey, $compIds, $ips) {
                // @phpstan-ignore method.notFound
                $query->withTrashed();
                // Check any of the ban details match the provided player details
                if ($ckey) {
                    $query->where('ckey', $ckey);
                } elseif ($compIds->isNotEmpty()) {
                    $query->whereIn('comp_id', $compIds);
                } elseif ($ips->isNotEmpty()) {
                    $query->whereIn('ip', $ips);
                }

                if ($ckey && $compIds->isNotEmpty()) {
                    $query->orWhereIn('comp_id', $compIds);
                }
                if ($ckey && $ips->isNotEmpty()) {
                    $query->orWhereIn('ip', $ips);
                }
            })
            ->orderBy('id', 'desc')
            ->get();

        return $bans;
    }

    /**
     * Add a ban
     */
    private function addBan(StoreRequest $request)
    {
        $data = collect($request->validated());

        $expiresAt = null;
        if ($data->has('duration')) {
            $duration = (int) $data['duration'];
            if ($duration > 0) {
                $expiresAt = Carbon::now()->addSeconds($duration);
            }
        }

        $player = null;
        $ckey = $data->has('ckey') ? ckey($data['ckey']) : null;
        if ($ckey) {
            $player = Player::where('ckey', $ckey)->first();
        }

        $gameAdmin = $request->getGameAdmin();
        $gameServer = $request->getGameServer();
        $gameServerGroup = $request->getGameServerGroup();

        if (! $gameServer) {
            // If the ban isn't targeting a specific server, the user wants it to apply to all servers
            // So we get the originating server group and apply it to the ban
            $gameServerGroup = app(CommonRequest::class)->fromServerGroup();
        }

        $ban = new Ban;
        $ban->round_id = $data->has('round_id') ? $data['round_id'] : null;
        $ban->reason = $data['reason'];
        $ban->expires_at = $expiresAt;
        $ban->requires_appeal = $data->has('requires_appeal') ? (bool) $data['requires_appeal'] : false;
        $ban->gameAdmin()->associate($gameAdmin);
        if ($gameServer) {
            $ban->gameServer()->associate($gameServer);
        } elseif ($gameServerGroup) {
            $ban->gameServerGroup()->associate($gameServerGroup);
        }
        $ban->save();

        $banDetail = new BanDetail;
        $banDetail->ckey = $ckey;
        $banDetail->comp_id = $data->has('comp_id') ? $data['comp_id'] : null;
        $banDetail->ip = $data->has('ip') ? $request['ip'] : null;
        $ban->details()->save($banDetail);

        $note = new PlayerNote;
        if ($player) {
            $note->player()->associate($player);
        } else {
            $note->ckey = $ckey;
        }
        $note->round_id = $data->has('round_id') ? $data['round_id'] : null;
        $note->note = sprintf(
            'Banned from %s %s. Reason: %s',
            is_null($gameServer) ? 'all servers' : $gameServer->server_id,
            $data->has('duration') && (int) $data['duration'] > 0
                ? 'for '.CarbonInterval::seconds($data['duration'])->cascade()->forHumans()
                : 'permanently',
            $data['reason']
        );
        $note->gameAdmin()->associate($gameAdmin);
        if ($gameServer) {
            $note->gameServer()->associate($gameServer);
        } elseif ($gameServerGroup) {
            $note->gameServerGroup()->associate($gameServerGroup);
        }
        $note->save();

        return new BanResource($ban);
    }

    /**
     * Update an existing ban
     */
    private function updateBan(StoreRequest $request, Ban $ban)
    {
        if ($ban->deleted_at) {
            throw new \Exception('This ban has already been removed.');
        }
        if ($ban->expires_at && $ban->expires_at->lte(Carbon::now())) {
            throw new \Exception('This ban has already expired.');
        }

        $data = collect($request->validated());

        $gameAdmin = $request->getGameAdmin();
        $gameServerGroup = $request->getGameServerGroup();

        $player = null;
        $ckey = $data->has('ckey') ? ckey($data['ckey']) : null;
        if ($ckey) {
            $player = Player::where('ckey', $ckey)->first();
        }

        $newBan = $data->only(['reason', 'requires_appeal']);

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
                // Ban is temporary, the new duration shall apply from right now
                // This is so we can add or reduce the duration if necessary
                $newExpiresAt = Carbon::now()->addSeconds($data['duration']);

                // Bans can't expire in the past
                if ($newExpiresAt->lte(Carbon::now())) {
                    throw new \Exception('The ban cannot expire in the past, please increase the duration.');
                }

                $newBan['expires_at'] = $newExpiresAt->toDateTimeString();
            }
        }

        $ban->update($newBan->toArray());
        $ban->originalBanDetail->update($data->only(['ckey', 'comp_id', 'ip'])->toArray());

        $note = new PlayerNote;
        if ($player) {
            $note->player()->associate($player);
        } else {
            $note->ckey = $ckey;
        }
        $note->round_id = $ban->round_id;
        $note->note = sprintf(
            'Edited ban. Server: %s. Duration: %s. Reason: %s. Computer ID: %s. IP: %s',
            is_null($ban->server_id) ? 'all servers' : $ban->server_id,
            $ban->expires_at
                ? $ban->expires_at->longAbsoluteDiffForHumans()
                : 'permanent',
            $data['reason'],
            $ban->originalBanDetail->comp_id,
            $ban->originalBanDetail->ip
        );
        $note->gameAdmin()->associate($gameAdmin);
        if ($ban->gameServer) {
            $note->gameServer()->associate($ban->gameServer);
        } elseif ($ban->gameServerGroup) {
            $note->gameServerGroup()->associate($ban->gameServerGroup);
        }
        $note->save();

        return new BanResource($ban);
    }
}
