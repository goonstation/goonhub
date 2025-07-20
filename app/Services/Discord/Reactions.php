<?php

namespace App\Services\Discord;

use Illuminate\Http\Client\Response;

class Reactions
{
    private Api $discordApi;

    private string $channelId;

    private string $messageId;

    public function __construct(Api $discordApi, string $channelId, string $messageId)
    {
        $this->discordApi = $discordApi;
        $this->channelId = $channelId;
        $this->messageId = $messageId;
    }

    /**
     * Get the channel ID
     */
    public function getChannelId(): string
    {
        return $this->channelId;
    }

    /**
     * Get the message ID
     */
    public function getMessageId(): string
    {
        return $this->messageId;
    }

    /**
     * Add a reaction to this message
     */
    public function add(string $emoji): Response
    {
        $emoji = urlencode($emoji);

        return $this->discordApi->put("channels/{$this->channelId}/messages/{$this->messageId}/reactions/{$emoji}/@me");
    }

    /**
     * Remove a reaction from this message
     */
    public function remove(string $emoji): Response
    {
        $emoji = urlencode($emoji);

        return $this->discordApi->delete("channels/{$this->channelId}/messages/{$this->messageId}/reactions/{$emoji}/@me");
    }

    /**
     * Get reactions for this message
     */
    public function get(string $emoji, array $query = []): Response
    {
        $emoji = urlencode($emoji);

        return $this->discordApi->get("channels/{$this->channelId}/messages/{$this->messageId}/reactions/{$emoji}", $query);
    }

    /**
     * Delete all reactions from this message
     */
    public function deleteAll(): Response
    {
        return $this->discordApi->delete("channels/{$this->channelId}/messages/{$this->messageId}/reactions");
    }

    /**
     * Delete all reactions of a specific emoji from this message
     */
    public function deleteAllForEmoji(string $emoji): Response
    {
        $emoji = urlencode($emoji);

        return $this->discordApi->delete("channels/{$this->channelId}/messages/{$this->messageId}/reactions/{$emoji}");
    }
}
