<?php

namespace App\Services\Discord;

use Illuminate\Http\Client\Response;

class Channel
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
     * Get channel information
     */
    public function get(): Response
    {
        return $this->discordApi->get("channels/{$this->channelId}");
    }

    /**
     * Update a channel
     */
    public function update(array $data, ?string $reason = null): Response
    {
        return $this->discordApi->patch("channels/{$this->channelId}", $data, [], $reason);
    }

    /**
     * Delete a channel
     */
    public function delete(?string $reason = null): Response
    {
        return $this->discordApi->delete("channels/{$this->channelId}", [], $reason);
    }

    /**
     * Get a message-specific API instance
     */
    public function message(string $messageId): Message
    {
        return new Message($this->discordApi, $this->channelId, $messageId);
    }

    /**
     * Get a messages API instance
     */
    public function messages(): Messages
    {
        return new Messages($this->discordApi, $this->channelId);
    }

    /**
     * Get a webhooks API instance
     */
    public function webhooks(): Webhooks
    {
        return new Webhooks($this->discordApi, $this->channelId);
    }

    /**
     * Get an invites API instance
     */
    public function invites(): Invites
    {
        return new Invites($this->discordApi, $this->channelId);
    }

    /**
     * Send a message to this channel
     */
    public function send(array $data): Response
    {
        return $this->discordApi->post("channels/{$this->channelId}/messages", $data);
    }

    /**
     * Get channel messages
     */
    public function getMessages(array $query = []): Response
    {
        return $this->discordApi->get("channels/{$this->channelId}/messages", $query);
    }

    /**
     * Create a webhook for this channel
     */
    public function createWebhook(array $data): Response
    {
        return $this->discordApi->post("channels/{$this->channelId}/webhooks", $data);
    }

    /**
     * Get channel invites
     */
    public function getInvites(): Response
    {
        return $this->discordApi->get("channels/{$this->channelId}/invites");
    }

    /**
     * Create a channel invite
     */
    public function createInvite(array $data, ?string $reason = null): Response
    {
        return $this->discordApi->post("channels/{$this->channelId}/invites", $data, [], $reason);
    }
}
