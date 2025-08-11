<?php

namespace App\Traits;

use App\Jobs\RecordPlayerConnection;
use App\Models\Player;

trait ManagesPlayers
{
    private function loginPlayer(Player $player, array $data)
    {
        if (isset($data['byond_major'])) {
            $player->byond_major = $data['byond_major'];
        }
        if (isset($data['byond_minor'])) {
            $player->byond_minor = $data['byond_minor'];
        }

        Player::withoutAuditing(function () use ($player) {
            return $player->save();
        });

        RecordPlayerConnection::dispatch($player->id, $data);

        return $player;
    }

    /**
     * Create a player with a given key, or with a unique suffix if the key is already taken.
     */
    private function createPlayer(string $key, string $suffixKey = 'byond'): Player
    {
        $attempts = 0;
        $canCreatePlayer = false;
        $key = Player::AUTH_SUFFIXES[$suffixKey] ? $key.'-'.Player::AUTH_SUFFIXES[$suffixKey] : $key;
        $keyToCreate = null;
        while (! $canCreatePlayer && $attempts < 10) {
            $keyToCreate = is_null($keyToCreate) ? $key : $key.rand(100, 999);
            $canCreatePlayer = Player::where('ckey', ckey($keyToCreate))->doesntExist();
            $attempts++;
        }

        if (! $canCreatePlayer) {
            throw new \Exception('Failed to create player');
        }

        return Player::withoutAuditing(function () use ($keyToCreate) {
            return Player::create(['ckey' => ckey($keyToCreate), 'key' => $keyToCreate]);
        });
    }
}
