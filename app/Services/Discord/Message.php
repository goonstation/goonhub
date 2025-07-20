<?php

namespace App\Services\Discord;

use Illuminate\Http\Client\Response;

class Message
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
     * Get the message ID
     */
    public function getMessageId(): string
    {
        return $this->messageId;
    }

    /**
     * Get the channel ID
     */
    public function getChannelId(): string
    {
        return $this->channelId;
    }

    /**
     * Get message information
     */
    public function get(): Response
    {
        return $this->discordApi->get("channels/{$this->channelId}/messages/{$this->messageId}");
    }

    /**
     * Update a message
     */
    public function update(array $data): Response
    {
        return $this->discordApi->patch("channels/{$this->channelId}/messages/{$this->messageId}", $data);
    }

    /**
     * Delete a message
     */
    public function delete(?string $reason = null): Response
    {
        return $this->discordApi->delete("channels/{$this->channelId}/messages/{$this->messageId}", [], $reason);
    }

    /**
     * Get a reactions API instance
     */
    public function reactions(): Reactions
    {
        return new Reactions($this->discordApi, $this->channelId, $this->messageId);
    }

    /**
     * Add a reaction to this message
     */
    public function addReaction(string $emoji): Response
    {
        $emoji = urlencode($emoji);

        return $this->discordApi->put("channels/{$this->channelId}/messages/{$this->messageId}/reactions/{$emoji}/@me");
    }

    /**
     * Remove a reaction from this message
     */
    public function removeReaction(string $emoji): Response
    {
        $emoji = urlencode($emoji);

        return $this->discordApi->delete("channels/{$this->channelId}/messages/{$this->messageId}/reactions/{$emoji}/@me");
    }

    /**
     * Get reactions for this message
     */
    public function getReactions(string $emoji, array $query = []): Response
    {
        $emoji = urlencode($emoji);

        return $this->discordApi->get("channels/{$this->channelId}/messages/{$this->messageId}/reactions/{$emoji}", $query);
    }

    /**
     * Delete all reactions from this message
     */
    public function deleteAllReactions(): Response
    {
        return $this->discordApi->delete("channels/{$this->channelId}/messages/{$this->messageId}/reactions");
    }

    /**
     * Delete all reactions of a specific emoji from this message
     */
    public function deleteAllReactionsForEmoji(string $emoji): Response
    {
        $emoji = urlencode($emoji);

        return $this->discordApi->delete("channels/{$this->channelId}/messages/{$this->messageId}/reactions/{$emoji}");
    }
}
