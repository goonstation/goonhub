<?php

namespace App\Services\Discord;

use Illuminate\Http\Client\Response;

class Guild
{
    private Api $discordApi;

    private string $guildId;

    public function __construct(Api $discordApi, string $guildId)
    {
        $this->discordApi = $discordApi;
        $this->guildId = $guildId;
    }

    /**
     * Get guild information
     */
    public function get(): Response
    {
        return $this->discordApi->get("guilds/{$this->guildId}");
    }

    /**
     * Get guild members
     */
    public function members(array $query = []): Response
    {
        return $this->discordApi->get("guilds/{$this->guildId}/members", $query);
    }

    /**
     * Get a specific guild member
     */
    public function member(string $userId): Response
    {
        return $this->discordApi->get("guilds/{$this->guildId}/members/{$userId}");
    }

    /**
     * Get guild roles
     */
    public function roles(): Response
    {
        return $this->discordApi->get("guilds/{$this->guildId}/roles");
    }

    /**
     * Create a guild role
     */
    public function createRole(array $data, ?string $reason = null): Response
    {
        return $this->discordApi->post("guilds/{$this->guildId}/roles", $data, [], $reason);
    }

    /**
     * Update a guild role
     */
    public function updateRole(string $roleId, array $data, ?string $reason = null): Response
    {
        return $this->discordApi->patch("guilds/{$this->guildId}/roles/{$roleId}", $data, [], $reason);
    }

    /**
     * Delete a guild role
     */
    public function deleteRole(string $roleId, ?string $reason = null): Response
    {
        return $this->discordApi->delete("guilds/{$this->guildId}/roles/{$roleId}", [], $reason);
    }

    /**
     * Add a role to a guild member
     */
    public function addMemberRole(string $userId, string $roleId, ?string $reason = null): Response
    {
        return $this->discordApi->put("guilds/{$this->guildId}/members/{$userId}/roles/{$roleId}", [], [], $reason);
    }

    /**
     * Remove a role from a guild member
     */
    public function removeMemberRole(string $userId, string $roleId, ?string $reason = null): Response
    {
        return $this->discordApi->delete("guilds/{$this->guildId}/members/{$userId}/roles/{$roleId}", [], $reason);
    }

    /**
     * Get guild channels
     */
    public function channels(): Response
    {
        return $this->discordApi->get("guilds/{$this->guildId}/channels");
    }

    /**
     * Create a guild channel
     */
    public function createChannel(array $data, ?string $reason = null): Response
    {
        return $this->discordApi->post("guilds/{$this->guildId}/channels", $data, [], $reason);
    }

    /**
     * Ban a guild member
     */
    public function banMember(string $userId, array $data = [], ?string $reason = null): Response
    {
        return $this->discordApi->put("guilds/{$this->guildId}/bans/{$userId}", $data, [], $reason);
    }

    /**
     * Unban a guild member
     */
    public function unbanMember(string $userId, ?string $reason = null): Response
    {
        return $this->discordApi->delete("guilds/{$this->guildId}/bans/{$userId}", [], $reason);
    }

    /**
     * Get guild bans
     */
    public function bans(array $query = []): Response
    {
        return $this->discordApi->get("guilds/{$this->guildId}/bans", $query);
    }

    /**
     * Get a guild ban
     */
    public function ban(string $userId): Response
    {
        return $this->discordApi->get("guilds/{$this->guildId}/bans/{$userId}");
    }

    /**
     * Kick a guild member
     */
    public function kickMember(string $userId, ?string $reason = null): Response
    {
        return $this->discordApi->delete("guilds/{$this->guildId}/members/{$userId}", [], $reason);
    }

    /**
     * Update a guild member
     */
    public function updateMember(string $userId, array $data, ?string $reason = null): Response
    {
        return $this->discordApi->patch("guilds/{$this->guildId}/members/{$userId}", $data, [], $reason);
    }

    /**
     * Get guild emojis
     */
    public function emojis(): Response
    {
        return $this->discordApi->get("guilds/{$this->guildId}/emojis");
    }

    /**
     * Get a guild emoji
     */
    public function emoji(string $emojiId): Response
    {
        return $this->discordApi->get("guilds/{$this->guildId}/emojis/{$emojiId}");
    }

    /**
     * Create a guild emoji
     */
    public function createEmoji(array $data, ?string $reason = null): Response
    {
        return $this->discordApi->post("guilds/{$this->guildId}/emojis", $data, [], $reason);
    }

    /**
     * Update a guild emoji
     */
    public function updateEmoji(string $emojiId, array $data, ?string $reason = null): Response
    {
        return $this->discordApi->patch("guilds/{$this->guildId}/emojis/{$emojiId}", $data, [], $reason);
    }

    /**
     * Delete a guild emoji
     */
    public function deleteEmoji(string $emojiId, ?string $reason = null): Response
    {
        return $this->discordApi->delete("guilds/{$this->guildId}/emojis/{$emojiId}", [], $reason);
    }

    /**
     * Get guild invites
     */
    public function invites(): Response
    {
        return $this->discordApi->get("guilds/{$this->guildId}/invites");
    }

    /**
     * Get guild voice regions
     */
    public function voiceRegions(): Response
    {
        return $this->discordApi->get("guilds/{$this->guildId}/regions");
    }

    /**
     * Get guild integrations
     */
    public function integrations(): Response
    {
        return $this->discordApi->get("guilds/{$this->guildId}/integrations");
    }

    /**
     * Delete a guild integration
     */
    public function deleteIntegration(string $integrationId, ?string $reason = null): Response
    {
        return $this->discordApi->delete("guilds/{$this->guildId}/integrations/{$integrationId}", [], $reason);
    }

    /**
     * Get guild widget
     */
    public function widget(): Response
    {
        return $this->discordApi->get("guilds/{$this->guildId}/widget");
    }

    /**
     * Update guild widget
     */
    public function updateWidget(array $data): Response
    {
        return $this->discordApi->patch("guilds/{$this->guildId}/widget", $data);
    }

    /**
     * Get guild widget JSON
     */
    public function widgetJson(): Response
    {
        return $this->discordApi->get("guilds/{$this->guildId}/widget.json");
    }

    /**
     * Get guild widget PNG
     */
    public function widgetPng(array $query = []): Response
    {
        return $this->discordApi->get("guilds/{$this->guildId}/widget.png", $query);
    }

    /**
     * Get guild vanity URL
     */
    public function vanityUrl(): Response
    {
        return $this->discordApi->get("guilds/{$this->guildId}/vanity-url");
    }

    /**
     * Get guild welcome screen
     */
    public function welcomeScreen(): Response
    {
        return $this->discordApi->get("guilds/{$this->guildId}/welcome-screen");
    }

    /**
     * Update guild welcome screen
     */
    public function updateWelcomeScreen(array $data): Response
    {
        return $this->discordApi->patch("guilds/{$this->guildId}/welcome-screen", $data);
    }

    /**
     * Get guild audit log
     */
    public function auditLog(array $query = []): Response
    {
        return $this->discordApi->get("guilds/{$this->guildId}/audit-logs", $query);
    }

    /**
     * Get guild templates
     */
    public function templates(): Response
    {
        return $this->discordApi->get("guilds/{$this->guildId}/templates");
    }

    /**
     * Create a guild template
     */
    public function createTemplate(array $data): Response
    {
        return $this->discordApi->post("guilds/{$this->guildId}/templates", $data);
    }

    /**
     * Sync a guild template
     */
    public function syncTemplate(string $templateCode): Response
    {
        return $this->discordApi->put("guilds/{$this->guildId}/templates/{$templateCode}");
    }

    /**
     * Update a guild template
     */
    public function updateTemplate(string $templateCode, array $data): Response
    {
        return $this->discordApi->patch("guilds/{$this->guildId}/templates/{$templateCode}", $data);
    }

    /**
     * Delete a guild template
     */
    public function deleteTemplate(string $templateCode): Response
    {
        return $this->discordApi->delete("guilds/{$this->guildId}/templates/{$templateCode}");
    }

    /**
     * Get guild scheduled events
     */
    public function scheduledEvents(array $query = []): Response
    {
        return $this->discordApi->get("guilds/{$this->guildId}/scheduled-events", $query);
    }

    /**
     * Create a guild scheduled event
     */
    public function createScheduledEvent(array $data, ?string $reason = null): Response
    {
        return $this->discordApi->post("guilds/{$this->guildId}/scheduled-events", $data, [], $reason);
    }

    /**
     * Get a guild scheduled event
     */
    public function scheduledEvent(string $eventId, array $query = []): Response
    {
        return $this->discordApi->get("guilds/{$this->guildId}/scheduled-events/{$eventId}", $query);
    }

    /**
     * Update a guild scheduled event
     */
    public function updateScheduledEvent(string $eventId, array $data, ?string $reason = null): Response
    {
        return $this->discordApi->patch("guilds/{$this->guildId}/scheduled-events/{$eventId}", $data, [], $reason);
    }

    /**
     * Delete a guild scheduled event
     */
    public function deleteScheduledEvent(string $eventId, ?string $reason = null): Response
    {
        return $this->discordApi->delete("guilds/{$this->guildId}/scheduled-events/{$eventId}", [], $reason);
    }

    /**
     * Get guild scheduled event users
     */
    public function scheduledEventUsers(string $eventId, array $query = []): Response
    {
        return $this->discordApi->get("guilds/{$this->guildId}/scheduled-events/{$eventId}/users", $query);
    }

    /**
     * Get guild sticker packs
     */
    public function stickers(): Response
    {
        return $this->discordApi->get("guilds/{$this->guildId}/stickers");
    }

    /**
     * Get a guild sticker
     */
    public function sticker(string $stickerId): Response
    {
        return $this->discordApi->get("guilds/{$this->guildId}/stickers/{$stickerId}");
    }

    /**
     * Create a guild sticker
     */
    public function createSticker(array $data, ?string $reason = null): Response
    {
        return $this->discordApi->post("guilds/{$this->guildId}/stickers", $data, [], $reason);
    }

    /**
     * Update a guild sticker
     */
    public function updateSticker(string $stickerId, array $data, ?string $reason = null): Response
    {
        return $this->discordApi->patch("guilds/{$this->guildId}/stickers/{$stickerId}", $data, [], $reason);
    }

    /**
     * Delete a guild sticker
     */
    public function deleteSticker(string $stickerId, ?string $reason = null): Response
    {
        return $this->discordApi->delete("guilds/{$this->guildId}/stickers/{$stickerId}", [], $reason);
    }

    /**
     * Get guild preview
     */
    public function preview(): Response
    {
        return $this->discordApi->get("guilds/{$this->guildId}/preview");
    }

    /**
     * Get guild prune count
     */
    public function pruneCount(array $query = []): Response
    {
        return $this->discordApi->get("guilds/{$this->guildId}/prune", $query);
    }

    /**
     * Begin guild prune
     */
    public function beginPrune(array $data, ?string $reason = null): Response
    {
        return $this->discordApi->post("guilds/{$this->guildId}/prune", $data, [], $reason);
    }

    /**
     * Get guild voice states
     */
    public function voiceStates(): Response
    {
        return $this->discordApi->get("guilds/{$this->guildId}/voice-states");
    }

    /**
     * Get guild voice state
     */
    public function voiceState(string $userId): Response
    {
        return $this->discordApi->get("guilds/{$this->guildId}/voice-states/{$userId}");
    }

    /**
     * Update user voice state
     */
    public function updateUserVoiceState(string $userId, array $data, ?string $reason = null): Response
    {
        return $this->discordApi->patch("guilds/{$this->guildId}/voice-states/{$userId}", $data, [], $reason);
    }

    /**
     * Get the guild ID being used
     */
    public function getGuildId(): string
    {
        return $this->guildId;
    }
}
