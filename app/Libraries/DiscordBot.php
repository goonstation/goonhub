<?php

namespace App\Libraries;

use Illuminate\Support\Facades\Http;

class DiscordBot
{
    public static function getConfig()
    {
        return [
            'url' => config('goonhub.discord_bot_url'),
            'key' => config('goonhub.discord_bot_key'),
        ];
    }

    /**
     * Export data to a Discord bot
     *
     * @return array|void
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public static function export(string $route, string $method, ?array $data = [])
    {
        $config = DiscordBot::getConfig();
        if (! $config['url']) {
            return;
        }
        $url = $config['url'];
        if (! is_array($data)) {
            $data = [];
        }
        $data['api_key'] = $config['key'];

        if (empty($data['server_name'])) {
            $data['server_name'] = 'Goonhub';
        }
        if (empty($data['server'])) {
            $data['server'] = 'goonhub';
        }

        $response = null;
        if ($method === 'POST') {
            $response = Http::withBody(json_encode($data), 'application/json')
                ->post("$url/$route");
        } else {
            $response = Http::get("$url/$route", $data);
        }

        return $response->throw()->json();
    }
}
