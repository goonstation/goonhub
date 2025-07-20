<?php

namespace App\Services\Discord;

use Illuminate\Http\Client\Response;

class Messages
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
     * Get channel messages
     */
    public function get(array $query = []): Response
    {
        return $this->discordApi->get("channels/{$this->channelId}/messages", $query);
    }

    /**
     * Create a message in this channel
     */
    public function create(array $data): Response
    {
        return $this->discordApi->post("channels/{$this->channelId}/messages", $data);
    }

    /**
     * Get a specific message
     */
    public function getMessage(string $messageId): Response
    {
        return $this->discordApi->get("channels/{$this->channelId}/messages/{$messageId}");
    }

    /**
     * Update a specific message
     */
    public function update(string $messageId, array $data): Response
    {
        return $this->discordApi->patch("channels/{$this->channelId}/messages/{$messageId}", $data);
    }

    /**
     * Delete a specific message
     */
    public function delete(string $messageId, ?string $reason = null): Response
    {
        return $this->discordApi->delete("channels/{$this->channelId}/messages/{$messageId}", [], $reason);
    }

    /**
     * Get a message-specific API instance
     */
    public function message(string $messageId): Message
    {
        return new Message($this->discordApi, $this->channelId, $messageId);
    }
}
