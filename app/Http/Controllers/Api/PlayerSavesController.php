<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlayerDataResource;
use App\Http\Resources\PlayerSaveResource;
use App\Models\Player;
use App\Models\PlayerData;
use App\Models\PlayerSave;
use App\Rules\PlayerIdWithCkey;
use Illuminate\Http\Request;
use Sentry\EventHint;

use function Sentry\captureMessage;

/**
 * @tags Player Saves
 */
class PlayerSavesController extends Controller
{
    /**
     * List data and saves
     *
     * List all data and saves for a player
     */
    public function index(Request $request)
    {
        $data = $request->validate([
            'player_id' => new PlayerIdWithCkey,
            'ckey' => 'required_without:player_id|alpha_num',
        ]);

        $playerId = isset($data['player_id']) ? $data['player_id'] : null;
        if (! $playerId) {
            $player = Player::where('ckey', $data['ckey'])->firstOrFail();
            $playerId = $player->id;
        }

        $cdata = PlayerData::where('player_id', $playerId)->get();
        $saves = PlayerSave::where('player_id', $playerId)->get();

        return [
            'data' => [
                'data' => PlayerDataResource::collection(new PlayerDataResource($cdata)),
                'saves' => PlayerSaveResource::collection(new PlayerSaveResource($saves)),
            ],
        ];
    }

    /**
     * Add data
     *
     * Add player data
     */
    public function storeData(Request $request)
    {
        $data = $request->validate([
            'player_id' => new PlayerIdWithCkey,
            'ckey' => 'required_without:player_id|alpha_num',
            'key' => 'required|string',
            'value' => 'nullable',
        ]);

        $playerId = isset($data['player_id']) ? $data['player_id'] : null;
        if (! $playerId) {
            $player = Player::where('ckey', $data['ckey'])->firstOrFail();
            $playerId = $player->id;
        }

        $cdata = PlayerData::updateOrCreate([
            'player_id' => $playerId,
            'key' => $data['key'],
        ], [
            'value' => $data['value'],
        ]);

        return new PlayerDataResource($cdata);
    }

    /**
     * Add save
     *
     * Add player save
     */
    public function storeFile(Request $request)
    {
        $data = $request->validate([
            'player_id' => new PlayerIdWithCkey,
            'ckey' => 'required_without:player_id|alpha_num',
            'name' => 'required|string',
            'data' => 'nullable',
        ]);

        if (strlen($data['data']) >= 51200) {
            return response()->json(['message' => 'Savefile exceeds 51200 bytes.'], 413);
        }

        $playerId = isset($data['player_id']) ? $data['player_id'] : null;
        if (! $playerId) {
            $player = Player::where('ckey', $data['ckey'])->firstOrFail();
            $playerId = $player->id;
        }

        $saveExists = PlayerSave::where('player_id', $playerId)->where('name', $data['name'])->first();
        if (! $saveExists) {
            $currentSaves = PlayerSave::where('player_id', $playerId)->count();
            if ($currentSaves >= 15) {
                return response()->json(['message' => 'Your account can only hold 15 savefiles.'], 400);
            }
        }

        $save = PlayerSave::updateOrCreate([
            'player_id' => $playerId,
            'name' => $data['name'],
        ], [
            'data' => $data['data'],
        ]);

        return [
            'data' => new PlayerSaveResource($save),
        ];
    }

    /**
     * Add data in bulk
     *
     * Add multiple entries of player data
     *
     * TODO: Describe json object
     */
    public function storeDataBulk(Request $request)
    {
        $data = $request->validate([
            'data' => 'required|json',
        ]);

        /**
         * [{"player_id": "12", "key": "foo", "value": "hello2"}, {"player_id": "12", "key": "foo2", "value": "bar4"}]
         */
        $bulkData = json_decode($data['data']);
        $dataToUpset = [];
        foreach ($bulkData as $item) {
            if ((! isset($item->player_id) && ! isset($item->ckey)) || ! isset($item->key)) {
                captureMessage('Invalid data during player saves storeDataBulk', null, EventHint::fromArray((array) $item));

                continue;
            }

            $playerId = $item->player_id;
            if (! $playerId && $item->ckey) {
                $player = Player::where('ckey', $item->ckey)->first();
                if (! $player) {
                    captureMessage('Invalid ckey during player saves storeDataBulk', null, EventHint::fromArray((array) $item));

                    continue;
                }
                $playerId = $player->id;
            }

            $dataToUpset[] = [
                'player_id' => $playerId,
                'key' => trim($item->key),
                'value' => $item->value,
            ];
        }

        PlayerData::upsert($dataToUpset, ['player_id', 'key'], ['value']);

        return ['message' => 'Success'];
    }

    /**
     * Transfer saves
     *
     * Transfer all save files from a player to another
     * WARNING: This overwrites all the saves for the target
     */
    public function transferSaves(Request $request)
    {
        $data = $request->validate([
            'from_ckey' => 'required|alpha_num',
            'to_ckey' => 'required|alpha_num',
        ]);

        // See who we're moving stuff from
        $fromPlayer = Player::where('ckey', $data['from_ckey'])->first();
        if (! $fromPlayer) {
            return response()->json(['message' => 'Unable to find source player'], 404);
        }

        // We allow player creation here so that admins can transfer saves to accounts that haven't connected before
        $toPlayer = Player::firstOrCreate(
            ['ckey' => $data['to_ckey']],
        );

        // Only transfer if there's stuff to move
        if (! PlayerSave::where('player_id', $fromPlayer->id)->exists()) {
            return response()->json(['message' => 'Source player has no saves to transfer'], 400);
        }

        // Delete the target's saves
        PlayerSave::where('player_id', $toPlayer->id)->delete();

        // Move files
        PlayerSave::where('player_id', $fromPlayer->id)
            ->update([
                'player_id' => $toPlayer->id,
            ]);

        return ['message' => 'Saves transferred'];
    }

    /**
     * Delete data
     *
     * Delete data for a player
     */
    public function destroyData(Request $request)
    {
        $data = $request->validate([
            'player_id' => new PlayerIdWithCkey,
            'ckey' => 'required_without:player_id|alpha_num',
            'key' => 'required',
        ]);

        $playerId = isset($data['player_id']) ? $data['player_id'] : null;
        if (! $playerId) {
            $player = Player::where('ckey', $data['ckey'])->firstOrFail();
            $playerId = $player->id;
        }

        PlayerData::where('player_id', $playerId)
            ->where('key', $data['key'])
            ->delete();

        return ['message' => 'Data removed'];
    }

    /**
     * Delete save
     *
     * Delete a save for a player
     */
    public function destroyFile(Request $request)
    {
        $data = $request->validate([
            'player_id' => new PlayerIdWithCkey,
            'ckey' => 'required_without:player_id|alpha_num',
            'name' => 'required',
        ]);

        $playerId = isset($data['player_id']) ? $data['player_id'] : null;
        if (! $playerId) {
            $player = Player::where('ckey', $data['ckey'])->firstOrFail();
            $playerId = $player->id;
        }

        PlayerSave::where('player_id', $playerId)
            ->where('name', $data['name'])
            ->delete();

        return ['message' => 'Save removed'];
    }
}
