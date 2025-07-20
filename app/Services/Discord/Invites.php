<?php

namespace App\Services\Discord;

use Illuminate\Http\Client\Response;

class Invites
{
    private Api $discordApi;

    private string $channelId;

    public function __construct(Api $discordApi, string $channelId)
    {
        $this->discordApi = $discordApi;
        $this->channelId = $channelId;
    }

    /**
     * Get the channel ID
     */
    public function getChannelId(): string
    {
        return $this->channelId;
    }

    /**
     * Get channel invites
     */
    public function get(): Response
    {
        return $this->discordApi->get("channels/{$this->channelId}/invites");
    }

    /**
     * Create a channel invite
     */
    public function create(array $data, ?string $reason = null): Response
    {
        return $this->discordApi->post("channels/{$this->channelId}/invites", $data, [], $reason);
    }
}
