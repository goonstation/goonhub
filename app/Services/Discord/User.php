<?php

namespace App\Services\Discord;

use Illuminate\Http\Client\Response;

class User
{
    private Api $discordApi;

    private ?string $userId;

    public function __construct(Api $discordApi, ?string $userId = null)
    {
        $this->discordApi = $discordApi;
        $this->userId = $userId;
    }

    /**
     * Get the user ID
     */
    public function getUserId(): ?string
    {
        return $this->userId;
    }

    /**
     * Get user information
     */
    public function get(): Response
    {
        if ($this->userId === null) {
            throw new \InvalidArgumentException('User ID is required for get() operation');
        }

        return $this->discordApi->get("users/{$this->userId}");
    }

    /**
     * Get current bot user information
     */
    public function me(): Response
    {
        return $this->discordApi->get('users/@me');
    }
}
