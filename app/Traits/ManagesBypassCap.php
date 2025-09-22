<?php

namespace App\Traits;

use App\Models\Player;
use App\Models\PlayerBypassCap;

trait ManagesBypassCap
{
    private function addPlayerToBypassCap(Player $player)
    {
        return $player->bypassCap()->firstOrCreate(['player_id' => $player->id]);
    }

    private function setBypassCapsByPlayer(Player $player, array $serverGroupIds, array $serverIds)
    {
        $bypassCapPlayer = $this->addPlayerToBypassCap($player);

        $bypassCapPlayer->serverGroups()->sync($serverGroupIds);
        $bypassCapPlayer->servers()->sync($serverIds);
    }

    private function setBypassCapsByPlayerIds(array $playerIds, array $serverGroupIds, array $serverIds)
    {
        foreach ($playerIds as $playerId) {
            $player = Player::find($playerId);
            if (! $player) {
                continue;
            }
            $bypassCapPlayer = $this->addPlayerToBypassCap($player);

            $bypassCapPlayer->serverGroups()->sync($serverGroupIds);
            $bypassCapPlayer->servers()->sync($serverIds);
        }
    }

    private function updatePlayerBypassCap(PlayerBypassCap $bypassCapPlayer, array $serverGroupIds, array $serverIds)
    {
        $bypassCapPlayer->serverGroups()->sync($serverGroupIds);
        $bypassCapPlayer->servers()->sync($serverIds);
    }

    private function removePlayerBypassCap(PlayerBypassCap $bypassCapPlayer)
    {
        $bypassCapPlayer->delete();
    }

    private function removeBypassCapsById(array $playerBypassCapIds)
    {
        PlayerBypassCap::whereIn('id', $playerBypassCapIds)->delete();
    }

    private function removeBypassCapsByPlayerId(array $playerIds)
    {
        PlayerBypassCap::whereIn('player_id', $playerIds)->delete();
    }

    private function removeBypassCapByPlayer(Player $player)
    {
        $player->bypassCap()->delete();
    }
}
