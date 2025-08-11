<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LinkedByondUser;
use App\Models\LinkedDiscordUser;
use App\Traits\ManagesUsers;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    use ManagesUsers;

    public function discordLink(Request $request)
    {
        $data = $request->validate([
            'discord_id' => 'required|string',
            'ckey' => 'required|string',
        ]);

        try {
            $this->legacyDiscordLink($data['discord_id'], $data['ckey']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['success' => 'User linked'], 200);
    }

    public function discordUnlink(Request $request)
    {
        $data = $request->validate([
            'discord_id' => 'required|string',
            'ckey' => 'required|string',
        ]);

        $linkedDiscord = LinkedDiscordUser::where('discord_id', $data['discord_id'])->first();
        $linkedByond = LinkedByondUser::where('ckey', $data['ckey'])->first();

        if ($linkedDiscord) {
            $linkedDiscord->delete();
        }

        if ($linkedByond) {
            $linkedByond->delete();
        }

        return response()->json(['success' => 'User unlinked'], 200);
    }
}
