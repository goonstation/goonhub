<?php

namespace App\Services\Discord;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class Api
{
    private string $baseUrl;

    private ?string $botToken;

    private int $timeout;

    private int $retryAttempts;

    private int $retryDelay;

    private ?string $globalGuildId;

    /**
     * Cache key prefix for rate limit tracking
     */
    private const RATE_LIMIT_CACHE_PREFIX = 'discord_rate_limit:';

    /**
     * Cache TTL for rate limit data (1 hour)
     */
    private const RATE_LIMIT_CACHE_TTL = 3600;

    public function __construct()
    {
        $config = config('services.discord.api');

        $this->baseUrl = rtrim($config['base_url'], '/');
        $this->botToken = $config['bot_token'];
        $this->timeout = $config['timeout'];
        $this->retryAttempts = $config['retry_attempts'];
        $this->retryDelay = $config['retry_delay'];
        $this->globalGuildId = $config['guild_id'] ?? null;
    }

    /**
     * Create a fresh HTTP client with Discord API configuration
     * This method is called for each request to ensure Octane compatibility
     */
    private function createHttpClient(?string $auditReason = null): PendingRequest
    {
        $headers = [
            'Accept' => 'application/json',
            'User-Agent' => 'Goonhub-Discord-API/1.0',
        ];

        if ($this->botToken) {
            $headers['Authorization'] = "Bot {$this->botToken}";
        }

        if ($auditReason !== null) {
            $headers['X-Audit-Log-Reason'] = $this->formatAuditReason($auditReason);
        }

        return Http::timeout($this->timeout)->withHeaders($headers);
    }

    /**
     * Format audit reason according to Discord's requirements
     * "1-512 URL-encoded UTF-8 characters"
     */
    private function formatAuditReason(string $reason): string
    {
        $trimmed = mb_substr(trim($reason), 0, 512);

        return $trimmed;
    }

    /**
     * Make a GET request to the Discord API
     */
    public function get(string $endpoint, array $query = []): Response
    {
        return $this->makeRequest('GET', $endpoint, [], $query);
    }

    /**
     * Make a POST request to the Discord API
     */
    public function post(string $endpoint, array $data = [], array $query = [], ?string $reason = null): Response
    {
        return $this->makeRequest('POST', $endpoint, $data, $query, $reason);
    }

    /**
     * Make a PUT request to the Discord API
     */
    public function put(string $endpoint, array $data = [], array $query = [], ?string $reason = null): Response
    {
        return $this->makeRequest('PUT', $endpoint, $data, $query, $reason);
    }

    /**
     * Make a PATCH request to the Discord API
     */
    public function patch(string $endpoint, array $data = [], array $query = [], ?string $reason = null): Response
    {
        return $this->makeRequest('PATCH', $endpoint, $data, $query, $reason);
    }

    /**
     * Make a DELETE request to the Discord API
     */
    public function delete(string $endpoint, array $query = [], ?string $reason = null): Response
    {
        return $this->makeRequest('DELETE', $endpoint, [], $query, $reason);
    }

    /**
     * Make a request with retry logic
     */
    private function makeRequest(string $method, string $endpoint, array $data = [], array $query = [], ?string $reason = null): Response
    {
        $url = $this->buildUrl($endpoint);
        $attempt = 0;
        $lastException = null;

        while ($attempt <= $this->retryAttempts) {
            try {
                // Create a fresh HTTP client for each request to ensure Octane compatibility
                $httpClient = $this->createHttpClient($reason);

                $response = match ($method) {
                    'GET' => $httpClient->get($url, $query),
                    'POST' => $httpClient->post($url, $data),
                    'PUT' => $httpClient->put($url, $data),
                    'PATCH' => $httpClient->patch($url, $data),
                    'DELETE' => $httpClient->delete($url, $query),
                    default => throw new InvalidArgumentException("Unsupported HTTP method: {$method}"),
                };

                // Log rate limit information
                $this->logRateLimitInfo($response);

                // If we get a 429, throw to trigger retry logic
                if ($response->status() === 429) {
                    throw new RequestException($response);
                }

                // For specific status codes, throwing here will skip the retry logic
                if ($response->status() === 404) {
                    throw new RequestException($response);
                }

                // If we get a 4xx (other than 429), throw after all retries are exhausted
                if ($response->status() >= 400 && $response->status() < 500) {
                    if ($attempt >= $this->retryAttempts) {
                        throw new RequestException($response);
                    } else {
                        // Retry for other 4xx
                        $lastException = new RequestException($response);
                        $attempt++;
                        usleep($this->retryDelay * 1000);

                        continue;
                    }
                }

                return $response;

            } catch (ConnectionException|RequestException $e) {
                $lastException = $e;
                $attempt++;

                // Handle rate limits specifically
                if ($e instanceof RequestException && $e->response->status() === 429) {
                    $retryDelay = $this->calculateRateLimitDelay($e->response, $endpoint);

                    if ($attempt <= $this->retryAttempts) {
                        Log::warning("Discord API rate limited, retrying in {$retryDelay}ms", [
                            'method' => $method,
                            'endpoint' => $endpoint,
                            'attempt' => $attempt,
                            'retry_after' => $e->response->header('Retry-After'),
                            'x_ratelimit_reset' => $e->response->header('X-RateLimit-Reset'),
                            'x_ratelimit_remaining' => $e->response->header('X-RateLimit-Remaining'),
                            'x_ratelimit_limit' => $e->response->header('X-RateLimit-Limit'),
                            'x_ratelimit_bucket' => $e->response->header('X-RateLimit-Bucket'),
                            'global' => $e->response->header('X-RateLimit-Global') === 'true',
                        ]);

                        usleep($retryDelay * 1000); // Convert to microseconds

                        continue;
                    }
                }

                // Don't retry on other client errors (4xx)
                if ($e instanceof RequestException && $e->response->status() >= 400 && $e->response->status() < 500) {
                    throw $e;
                }

                if ($attempt <= $this->retryAttempts) {
                    Log::warning("Discord API request failed, retrying in {$this->retryDelay}ms", [
                        'method' => $method,
                        'endpoint' => $endpoint,
                        'attempt' => $attempt,
                        'error' => $e->getMessage(),
                    ]);

                    usleep($this->retryDelay * 1000); // Convert to microseconds
                }
            }
        }

        throw $lastException;
    }

    /**
     * Calculate the appropriate delay for rate limit handling
     */
    private function calculateRateLimitDelay(Response $response, string $endpoint): int
    {
        // Check if this is a global rate limit
        $isGlobal = $response->header('X-RateLimit-Global') === 'true';

        if ($isGlobal) {
            // For global rate limits, use the Retry-After header
            $retryAfter = $response->header('Retry-After');
            if ($retryAfter !== '') {
                $delay = (int) $retryAfter * 1000; // Convert seconds to milliseconds

                // In testing environment, allow very short delays
                if (app()->environment('testing') && $delay === 0) {
                    return 1; // 1ms delay for testing
                }

                return $delay;
            }

            // Fallback delay for global rate limits
            return 5000; // 5 seconds
        }

        // For route-specific rate limits, use X-RateLimit-Reset
        $resetTime = $response->header('X-RateLimit-Reset');
        if ($resetTime !== '') {
            $resetTimestamp = (int) $resetTime;
            $currentTime = time();
            $delay = ($resetTimestamp - $currentTime) * 1000; // Convert to milliseconds

            // Add a small buffer to ensure we're past the reset time
            $delay += 1000; // Add 1 second buffer

            // In testing environment, allow very short delays
            if (app()->environment('testing') && $delay <= 1000) {
                return 1; // 1ms delay for testing
            }

            // Ensure minimum delay
            return max($delay, 1000);
        }

        // Fallback delay for route-specific rate limits
        return 2000; // 2 seconds
    }

    /**
     * Build the full URL for the endpoint
     */
    private function buildUrl(string $endpoint): string
    {
        $endpoint = ltrim($endpoint, '/');

        return "{$this->baseUrl}/{$endpoint}";
    }

    /**
     * Log rate limit information from response headers
     */
    private function logRateLimitInfo(Response $response): void
    {
        $remaining = $response->header('X-RateLimit-Remaining');
        $reset = $response->header('X-RateLimit-Reset');
        $limit = $response->header('X-RateLimit-Limit');
        $bucket = $response->header('X-RateLimit-Bucket');
        $global = $response->header('X-RateLimit-Global');

        // Track rate limit information per bucket
        if ($bucket !== '') {
            $cacheKey = self::RATE_LIMIT_CACHE_PREFIX.$bucket;
            $cacheData = [
                'remaining' => (int) $remaining,
                'limit' => (int) $limit,
                'reset' => (int) $reset,
                'last_updated' => time(),
                'bucket' => $bucket,
                'global' => $global === 'true',
            ];

            // Calculate TTL based on reset time
            $currentTime = time();
            $resetTime = (int) $reset;
            $ttl = max(60, $resetTime - $currentTime + 300); // Add 5 minutes buffer

            Cache::put($cacheKey, $cacheData, $ttl);

            // Also store bucket in a list for easy retrieval
            $this->addBucketToList($bucket);
        }

        // Log when approaching rate limits (remaining < 10% of limit)
        if ($remaining !== '' && $limit !== '') {
            $remainingInt = (int) $remaining;
            $limitInt = (int) $limit;
            $percentage = ($remainingInt / $limitInt) * 100;

            if ($percentage < 10) {
                Log::warning('Discord API rate limit approaching', [
                    'remaining' => $remaining,
                    'limit' => $limit,
                    'percentage' => round($percentage, 2),
                    'reset' => $reset,
                    'bucket' => $bucket,
                    'global' => $global,
                    'reset_time' => $reset ? date('Y-m-d H:i:s', (int) $reset) : null,
                ]);
            }
        }

        // Log when very close to rate limit (remaining < 5)
        if ($remaining !== '' && (int) $remaining < 5) {
            Log::error('Discord API rate limit critical', [
                'remaining' => $remaining,
                'limit' => $limit,
                'reset' => $reset,
                'bucket' => $bucket,
                'global' => $global,
                'reset_time' => $reset ? date('Y-m-d H:i:s', (int) $reset) : null,
            ]);
        }
    }

    /**
     * Add bucket to the list of tracked buckets
     */
    private function addBucketToList(string $bucket): void
    {
        $listKey = self::RATE_LIMIT_CACHE_PREFIX.'buckets';
        $buckets = Cache::get($listKey, []);

        if (! in_array($bucket, $buckets)) {
            $buckets[] = $bucket;
            Cache::put($listKey, $buckets, self::RATE_LIMIT_CACHE_TTL);
        }
    }

    /**
     * Get current rate limit status for all tracked buckets
     */
    public function getRateLimitStatus(): array
    {
        $status = [];
        $currentTime = time();
        $listKey = self::RATE_LIMIT_CACHE_PREFIX.'buckets';
        $buckets = Cache::get($listKey, []);

        foreach ($buckets as $bucket) {
            $cacheKey = self::RATE_LIMIT_CACHE_PREFIX.$bucket;
            $info = Cache::get($cacheKey);

            if ($info) {
                $timeUntilReset = max(0, $info['reset'] - $currentTime);
                $percentage = $info['limit'] > 0 ? ($info['remaining'] / $info['limit']) * 100 : 0;

                $status[$bucket] = [
                    'remaining' => $info['remaining'],
                    'limit' => $info['limit'],
                    'percentage' => round($percentage, 2),
                    'reset_time' => date('Y-m-d H:i:s', $info['reset']),
                    'time_until_reset' => $timeUntilReset,
                    'last_updated' => date('Y-m-d H:i:s', $info['last_updated']),
                    'is_critical' => $info['remaining'] < 5,
                    'is_low' => $percentage < 10,
                    'global' => $info['global'] ?? false,
                ];
            }
        }

        return $status;
    }

    /**
     * Clear rate limit tracking data
     */
    public function clearRateLimitTracking(): void
    {
        $listKey = self::RATE_LIMIT_CACHE_PREFIX.'buckets';
        $buckets = Cache::get($listKey, []);

        foreach ($buckets as $bucket) {
            $cacheKey = self::RATE_LIMIT_CACHE_PREFIX.$bucket;
            Cache::forget($cacheKey);
        }

        Cache::forget($listKey);
    }

    /**
     * Get rate limit status for a specific bucket
     */
    public function getBucketRateLimitStatus(string $bucket): ?array
    {
        $cacheKey = self::RATE_LIMIT_CACHE_PREFIX.$bucket;
        $info = Cache::get($cacheKey);

        if (! $info) {
            return null;
        }

        $currentTime = time();
        $timeUntilReset = max(0, $info['reset'] - $currentTime);
        $percentage = $info['limit'] > 0 ? ($info['remaining'] / $info['limit']) * 100 : 0;

        return [
            'remaining' => $info['remaining'],
            'limit' => $info['limit'],
            'percentage' => round($percentage, 2),
            'reset_time' => date('Y-m-d H:i:s', $info['reset']),
            'time_until_reset' => $timeUntilReset,
            'last_updated' => date('Y-m-d H:i:s', $info['last_updated']),
            'is_critical' => $info['remaining'] < 5,
            'is_low' => $info['remaining'] < 10,
            'global' => $info['global'] ?? false,
        ];
    }

    /**
     * Check if the bot token is configured
     */
    public function hasBotToken(): bool
    {
        return ! empty($this->botToken);
    }

    /**
     * Get the configured base URL
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Resolve guild ID, using global guild ID as fallback
     */
    private function resolveGuildId(?string $guildId = null): string
    {
        if ($guildId !== null) {
            return $guildId;
        }

        if ($this->globalGuildId === null) {
            throw new InvalidArgumentException('Guild ID is required but no global guild ID is configured');
        }

        return $this->globalGuildId;
    }

    /**
     * Check if a global guild ID is configured
     */
    public function hasGlobalGuildId(): bool
    {
        return $this->globalGuildId !== null;
    }

    /**
     * Get the configured global guild ID
     */
    public function getGlobalGuildId(): ?string
    {
        return $this->globalGuildId;
    }

    /**
     * Get a guild-specific API instance for fluent operations
     */
    public function guild(?string $guildId = null): Guild
    {
        $resolvedGuildId = $this->resolveGuildId($guildId);

        return new Guild($this, $resolvedGuildId);
    }

    /**
     * Get a channel-specific API instance for fluent operations
     */
    public function channel(string $channelId): Channel
    {
        return new Channel($this, $channelId);
    }

    /**
     * Get a user-specific API instance for fluent operations
     */
    public function user(?string $userId = null): User
    {
        return new User($this, $userId);
    }

    /**
     * Get an invite-specific API instance for fluent operations
     */
    public function invite(string $inviteCode): Invite
    {
        return new Invite($this, $inviteCode);
    }

    /**
     * Get an application-specific API instance for fluent operations
     */
    public function application(string $applicationId): Application
    {
        return new Application($this, $applicationId);
    }
}
