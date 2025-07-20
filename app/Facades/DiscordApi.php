<?php

namespace App\Facades;

use App\Services\Discord\Api as DiscordApiService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Http\Client\Response get(string $endpoint, array $query = [])
 * @method static \Illuminate\Http\Client\Response post(string $endpoint, array $data = [], array $query = [], ?string $reason = null)
 * @method static \Illuminate\Http\Client\Response put(string $endpoint, array $data = [], array $query = [], ?string $reason = null)
 * @method static \Illuminate\Http\Client\Response patch(string $endpoint, array $data = [], array $query = [], ?string $reason = null)
 * @method static \Illuminate\Http\Client\Response delete(string $endpoint, array $query = [], ?string $reason = null)
 * @method static bool hasBotToken()
 * @method static string getBaseUrl()
 * @method static bool hasGlobalGuildId()
 * @method static ?string getGlobalGuildId()
 * @method static \App\Services\Discord\Guild guild(?string $guildId = null)
 * @method static \App\Services\Discord\Channel channel(string $channelId)
 * @method static \App\Services\Discord\User user(?string $userId = null)
 * @method static \App\Services\Discord\Invite invite(string $inviteCode)
 * @method static \App\Services\Discord\Application application(string $applicationId)
 * @method static array getRateLimitStatus()
 * @method static ?array getBucketRateLimitStatus(string $bucket)
 * @method static void clearRateLimitTracking()
 *
 * @see DiscordApiService
 */
class DiscordApi extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return DiscordApiService::class;
    }
}
