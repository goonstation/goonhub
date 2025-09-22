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

    private function setWhitelistsByPlayer(Player $player, array $serverGroupIds, array $serverIds)
    {
        $whitelistedPlayer = $this->addPlayerToWhitelist($player);

        $whitelistedPlayer->serverGroups()->sync($serverGroupIds);
        $whitelistedPlayer->servers()->sync($serverIds);
    }

    private function setWhitelistsByPlayerIds(array $playerIds, array $serverGroupIds, array $serverIds)
    {
        foreach ($playerIds as $playerId) {
            $player = Player::find($playerId);
            if (! $player) {
                continue;
            }
            $whitelistedPlayer = $this->addPlayerToWhitelist($player);

            $whitelistedPlayer->serverGroups()->sync($serverGroupIds);
            $whitelistedPlayer->servers()->sync($serverIds);
        }
    }

    private function updatePlayerWhitelist(PlayerWhitelist $whitelistedPlayer, array $serverGroupIds, array $serverIds)
    {
        $whitelistedPlayer->serverGroups()->sync($serverGroupIds);
        $whitelistedPlayer->servers()->sync($serverIds);
    }

    private function removePlayerWhitelist(PlayerWhitelist $whitelistedPlayer)
    {
        $whitelistedPlayer->delete();
    }

    private function removeWhitelistsById(array $playerWhitelistIds)
    {
        PlayerWhitelist::whereIn('id', $playerWhitelistIds)->delete();
    }

    private function removeWhitelistsByPlayerId(array $playerIds)
    {
        PlayerWhitelist::whereIn('player_id', $playerIds)->delete();
    }

    private function removeWhitelistByPlayer(Player $player)
    {
        $player->whitelist()->delete();
    }
}
