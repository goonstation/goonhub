<?php

namespace App\Services\Discord;

use Illuminate\Http\Client\Response;

class Webhook
{
    private Api $discordApi;

    private string $webhookId;

    public function __construct(Api $discordApi, string $webhookId)
    {
        $this->discordApi = $discordApi;
        $this->webhookId = $webhookId;
    }

    /**
     * Get the webhook ID
     */
    public function getWebhookId(): string
    {
        return $this->webhookId;
    }

    /**
     * Get webhook information
     */
    public function get(): Response
    {
        return $this->discordApi->get("webhooks/{$this->webhookId}");
    }

    /**
     * Update a webhook
     */
    public function update(array $data, ?string $reason = null): Response
    {
        return $this->discordApi->patch("webhooks/{$this->webhookId}", $data, [], $reason);
    }

    /**
     * Delete a webhook
     */
    public function delete(?string $reason = null): Response
    {
        return $this->discordApi->delete("webhooks/{$this->webhookId}", [], $reason);
    }

    /**
     * Execute a webhook with token
     */
    public function execute(string $webhookToken, array $data): Response
    {
        return $this->discordApi->post("webhooks/{$this->webhookId}/{$webhookToken}", $data);
    }

    /**
     * Update a webhook with token
     */
    public function updateWithToken(string $webhookToken, array $data, ?string $reason = null): Response
    {
        return $this->discordApi->patch("webhooks/{$this->webhookId}/{$webhookToken}", $data, [], $reason);
    }

    /**
     * Delete a webhook with token
     */
    public function deleteWithToken(string $webhookToken, ?string $reason = null): Response
    {
        return $this->discordApi->delete("webhooks/{$this->webhookId}/{$webhookToken}", [], $reason);
    }
}
