<?php

namespace App\Services\Discord;

use Illuminate\Http\Client\Response;

class Invite
{
    private Api $discordApi;

    private string $inviteCode;

    public function __construct(Api $discordApi, string $inviteCode)
    {
        $this->discordApi = $discordApi;
        $this->inviteCode = $inviteCode;
    }

    /**
     * Get the invite code
     */
    public function getInviteCode(): string
    {
        return $this->inviteCode;
    }

    /**
     * Get invite information
     */
    public function get(array $query = []): Response
    {
        return $this->discordApi->get("invites/{$this->inviteCode}", $query);
    }

    /**
     * Delete an invite
     */
    public function delete(?string $reason = null): Response
    {
        return $this->discordApi->delete("invites/{$this->inviteCode}", [], $reason);
    }
}
