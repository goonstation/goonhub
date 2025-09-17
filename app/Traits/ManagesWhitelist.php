<?php

namespace App\Traits;

use App\Models\Player;
use App\Models\PlayerWhitelist;

trait ManagesWhitelist
{
    private function addPlayerToWhitelist(Player $player)
    {
        return $player->whitelist()->firstOrCreate(['player_id' => $player->id]);
    }

    private function setPlayerWhitelistServers(Player $player, array $serverIds)
    {
        $whitelistedPlayer = $this->addPlayerToWhitelist($player);

        $whitelistedPlayer->servers()->sync($serverIds);
    }

    private function setPlayersWhitelistServers(array $playerIds, array $serverIds)
    {
        foreach ($playerIds as $playerId) {
            $player = Player::find($playerId);
            if (! $player) {
                continue;
            }
            $whitelistedPlayer = $this->addPlayerToWhitelist($player);

            $whitelistedPlayer->servers()->sync($serverIds);
        }
    }

    private function updatePlayerWhitelistServers(PlayerWhitelist $whitelistedPlayer, array $serverIds)
    {
        $whitelistedPlayer->servers()->sync($serverIds);
    }

    private function removePlayerWhitelist(PlayerWhitelist $whitelistedPlayer)
    {
        $whitelistedPlayer->delete();
    }

    private function removePlayerWhitelists(array $playerWhitelistIds)
    {
        PlayerWhitelist::whereIn('id', $playerWhitelistIds)->delete();
    }
}
