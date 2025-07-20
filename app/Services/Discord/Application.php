<?php

namespace App\Services\Discord;

use Illuminate\Http\Client\Response;

class Application
{
    private Api $discordApi;

    private string $applicationId;

    public function __construct(Api $discordApi, string $applicationId)
    {
        $this->discordApi = $discordApi;
        $this->applicationId = $applicationId;
    }

    /**
     * Get the application ID
     */
    public function getApplicationId(): string
    {
        return $this->applicationId;
    }

    /**
     * Get application commands for this application
     */
    public function commands(array $query = []): Response
    {
        return $this->discordApi->get("applications/{$this->applicationId}/commands", $query);
    }

    /**
     * Create an application command
     */
    public function createCommand(array $data): Response
    {
        return $this->discordApi->post("applications/{$this->applicationId}/commands", $data);
    }

    /**
     * Get a specific application command
     */
    public function command(string $commandId): Response
    {
        return $this->discordApi->get("applications/{$this->applicationId}/commands/{$commandId}");
    }

    /**
     * Update an application command
     */
    public function updateCommand(string $commandId, array $data): Response
    {
        return $this->discordApi->patch("applications/{$this->applicationId}/commands/{$commandId}", $data);
    }

    /**
     * Delete an application command
     */
    public function deleteCommand(string $commandId): Response
    {
        return $this->discordApi->delete("applications/{$this->applicationId}/commands/{$commandId}");
    }

    /**
     * Bulk overwrite application commands
     */
    public function bulkOverwriteCommands(array $commands): Response
    {
        return $this->discordApi->put("applications/{$this->applicationId}/commands", $commands);
    }

    /**
     * Get guild-specific application commands
     */
    public function guildCommands(string $guildId, array $query = []): Response
    {
        return $this->discordApi->get("applications/{$this->applicationId}/guilds/{$guildId}/commands", $query);
    }

    /**
     * Create a guild-specific application command
     */
    public function createGuildCommand(string $guildId, array $data): Response
    {
        return $this->discordApi->post("applications/{$this->applicationId}/guilds/{$guildId}/commands", $data);
    }

    /**
     * Get a specific guild application command
     */
    public function guildCommand(string $guildId, string $commandId): Response
    {
        return $this->discordApi->get("applications/{$this->applicationId}/guilds/{$guildId}/commands/{$commandId}");
    }

    /**
     * Update a guild application command
     */
    public function updateGuildCommand(string $guildId, string $commandId, array $data): Response
    {
        return $this->discordApi->patch("applications/{$this->applicationId}/guilds/{$guildId}/commands/{$commandId}", $data);
    }

    /**
     * Delete a guild application command
     */
    public function deleteGuildCommand(string $guildId, string $commandId): Response
    {
        return $this->discordApi->delete("applications/{$this->applicationId}/guilds/{$guildId}/commands/{$commandId}");
    }

    /**
     * Bulk overwrite guild application commands
     */
    public function bulkOverwriteGuildCommands(string $guildId, array $commands): Response
    {
        return $this->discordApi->put("applications/{$this->applicationId}/guilds/{$guildId}/commands", $commands);
    }

    /**
     * Get application command permissions for a guild
     */
    public function guildCommandPermissions(string $guildId): Response
    {
        return $this->discordApi->get("applications/{$this->applicationId}/guilds/{$guildId}/commands/permissions");
    }

    /**
     * Get application command permissions for a specific command
     */
    public function guildCommandPermission(string $guildId, string $commandId): Response
    {
        return $this->discordApi->get("applications/{$this->applicationId}/guilds/{$guildId}/commands/{$commandId}/permissions");
    }

    /**
     * Edit application command permissions
     */
    public function editGuildCommandPermissions(string $guildId, string $commandId, array $data): Response
    {
        return $this->discordApi->put("applications/{$this->applicationId}/guilds/{$guildId}/commands/{$commandId}/permissions", $data);
    }

    /**
     * Batch edit application command permissions
     */
    public function batchEditGuildCommandPermissions(string $guildId, array $permissions): Response
    {
        return $this->discordApi->put("applications/{$this->applicationId}/guilds/{$guildId}/commands/permissions", $permissions);
    }
}
