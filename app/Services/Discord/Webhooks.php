<?php

namespace App\Services\Discord;

use Illuminate\Http\Client\Response;

class Webhooks
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
     * Get channel webhooks
     */
    public function get(): Response
    {
        return $this->discordApi->get("channels/{$this->channelId}/webhooks");
    }

    /**
     * Create a webhook for this channel
     */
    public function create(array $data): Response
    {
        return $this->discordApi->post("channels/{$this->channelId}/webhooks", $data);
    }

    /**
     * Get a webhook-specific API instance
     */
    public function webhook(string $webhookId): Webhook
    {
        return new Webhook($this->discordApi, $webhookId);
    }
}
