<?php

namespace App\Http\Controllers\Api;

use App\Enums\DiscordSettings;
use App\Http\Controllers\Controller;
use App\Models\DiscordSetting;
use DiscordApi;
use Illuminate\Http\Request;

class TestController extends Controller
{
    /**
     * Test
     */
    public function index(Request $request)
    {
        $guildId = DiscordSetting::where('key', DiscordSettings::TOMATO_GUILD_ID->value)->first()?->value;
        $guild = DiscordApi::guild($guildId);

        return response()->json([
            'message' => $guild->members()->json(),
        ]);
    }
}
