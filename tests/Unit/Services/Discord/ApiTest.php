<?php

namespace Tests\Unit\Services\Discord;

use App\Services\Discord\Api;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Tests\TestCase;

class ApiTest extends TestCase
{
    private Api $discordApi;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up test configuration
        Config::set('services.discord.api', [
            'base_url' => 'https://discord.com/api/v10',
            'bot_token' => 'test-bot-token',
            'timeout' => 30,
            'retry_attempts' => 3,
            'retry_delay' => 1000,
        ]);

        $this->discordApi = new Api;
    }

    public function test_constructor_loads_configuration()
    {
        $this->assertEquals('https://discord.com/api/v10', $this->discordApi->getBaseUrl());
        $this->assertTrue($this->discordApi->hasBotToken());
    }

    public function test_constructor_without_bot_token()
    {
        Config::set('services.discord.api.bot_token', null);

        $discordApi = new Api;

        $this->assertFalse($discordApi->hasBotToken());
    }

    public function test_constructor_with_global_guild_id()
    {
        Config::set('services.discord.api.guild_id', '123456789');

        $discordApi = new Api;

        $this->assertTrue($discordApi->hasGlobalGuildId());
        $this->assertEquals('123456789', $discordApi->getGlobalGuildId());
    }

    public function test_constructor_without_global_guild_id()
    {
        Config::set('services.discord.api.guild_id', null);

        $discordApi = new Api;

        $this->assertFalse($discordApi->hasGlobalGuildId());
        $this->assertNull($discordApi->getGlobalGuildId());
    }

    public function test_fluent_guild_api_with_global_guild_id()
    {
        Config::set('services.discord.api.guild_id', '123456789');

        $discordApi = new Api;

        Http::fake([
            'https://discord.com/api/v10/guilds/123456789*' => Http::response(['id' => '123456789'], 200),
            'https://discord.com/api/v10/guilds/123456789/members*' => Http::response([['user' => ['id' => '123']]], 200),
            'https://discord.com/api/v10/guilds/123456789/roles*' => Http::response([['id' => 'role1']], 200),
        ]);

        $guildApi = $discordApi->guild();

        $this->assertEquals('123456789', $guildApi->getGuildId());
        $this->assertEquals(200, $guildApi->get()->status());
        $this->assertEquals(200, $guildApi->members()->status());
        $this->assertEquals(200, $guildApi->roles()->status());
    }

    public function test_fluent_guild_api_with_explicit_guild_id()
    {
        Config::set('services.discord.api.guild_id', '123456789');

        $discordApi = new Api;

        Http::fake([
            'https://discord.com/api/v10/guilds/987654321*' => Http::response(['id' => '987654321'], 200),
        ]);

        $guildApi = $discordApi->guild('987654321');

        $this->assertEquals('987654321', $guildApi->getGuildId());
        $this->assertEquals(200, $guildApi->get()->status());
    }

    public function test_fluent_guild_api_throws_exception_when_no_guild_id_available()
    {
        Config::set('services.discord.api.guild_id', null);

        $discordApi = new Api;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Guild ID is required but no global guild ID is configured');

        $discordApi->guild();
    }

    public function test_get_request()
    {
        Http::fake([
            'https://discord.com/api/v10/test-endpoint*' => Http::response(['success' => true], 200),
        ]);

        $response = $this->discordApi->get('test-endpoint');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->status());
        $this->assertEquals(['success' => true], $response->json());
    }

    public function test_post_request()
    {
        Http::fake([
            'https://discord.com/api/v10/test-endpoint*' => Http::response(['id' => '123'], 201),
        ]);

        $data = ['name' => 'test'];
        $response = $this->discordApi->post('test-endpoint', $data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(201, $response->status());
        $this->assertEquals(['id' => '123'], $response->json());
    }

    public function test_put_request()
    {
        Http::fake([
            'https://discord.com/api/v10/test-endpoint*' => Http::response(['updated' => true], 200),
        ]);

        $data = ['name' => 'updated'];
        $response = $this->discordApi->put('test-endpoint', $data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->status());
    }

    public function test_patch_request()
    {
        Http::fake([
            'https://discord.com/api/v10/test-endpoint*' => Http::response(['patched' => true], 200),
        ]);

        $data = ['name' => 'patched'];
        $response = $this->discordApi->patch('test-endpoint', $data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->status());
    }

    public function test_delete_request()
    {
        Http::fake([
            'https://discord.com/api/v10/test-endpoint*' => Http::response(null, 204),
        ]);

        $response = $this->discordApi->delete('test-endpoint');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(204, $response->status());
    }

    public function test_invalid_http_method_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported HTTP method: INVALID');

        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->discordApi);
        $method = $reflection->getMethod('makeRequest');
        $method->setAccessible(true);

        $method->invoke($this->discordApi, 'INVALID', 'test-endpoint');
    }

    public function test_request_with_query_parameters()
    {
        Http::fake([
            'https://discord.com/api/v10/test-endpoint*' => Http::response(['data' => []], 200),
        ]);

        $query = ['limit' => 10, 'offset' => 0];
        $response = $this->discordApi->get('test-endpoint', $query);

        $this->assertEquals(200, $response->status());
        Http::assertSent(function ($request) use ($query) {
            $url = $request->url();
            parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $actualQuery);

            return str_starts_with($url, 'https://discord.com/api/v10/test-endpoint')
                && $request->method() === 'GET'
                && $actualQuery == $query;
        });
    }

    public function test_retry_logic_on_connection_exception()
    {
        Http::fake([
            'https://discord.com/api/v10/test-endpoint' => function () {
                static $count = 0;
                $count++;
                if ($count <= 2) {
                    throw new ConnectionException('Connection failed');
                }

                return Http::response(['success' => true], 200);
            },
        ]);

        $response = $this->discordApi->get('test-endpoint');

        $this->assertEquals(200, $response->status());
        $this->assertEquals(['success' => true], $response->json());
    }

    public function test_retry_logic_on_rate_limit()
    {
        Http::fake([
            'https://discord.com/api/v10/test-endpoint' => Http::sequence()
                ->push(['error' => 'rate limited'], 429, ['Retry-After' => '1'])
                ->push(['success' => true], 200),
        ]);

        $response = $this->discordApi->get('test-endpoint');

        $this->assertEquals(200, $response->status());
        $this->assertEquals(['success' => true], $response->json());
    }

    public function test_no_retry_on_client_errors()
    {
        Http::fake([
            'https://discord.com/api/v10/test-endpoint' => Http::response(['error' => 'not found'], 404),
        ]);

        $this->expectException(RequestException::class);

        $this->discordApi->get('test-endpoint');
    }

    public function test_build_url_with_leading_slash()
    {
        $reflection = new \ReflectionClass($this->discordApi);
        $method = $reflection->getMethod('buildUrl');
        $method->setAccessible(true);

        $url = $method->invoke($this->discordApi, '/test-endpoint');

        $this->assertEquals('https://discord.com/api/v10/test-endpoint', $url);
    }

    public function test_build_url_without_leading_slash()
    {
        $reflection = new \ReflectionClass($this->discordApi);
        $method = $reflection->getMethod('buildUrl');
        $method->setAccessible(true);

        $url = $method->invoke($this->discordApi, 'test-endpoint');

        $this->assertEquals('https://discord.com/api/v10/test-endpoint', $url);
    }

    public function test_http_client_without_bot_token()
    {
        Config::set('services.discord.api.bot_token', null);

        $discordApi = new Api;

        Http::fake([
            'https://discord.com/api/v10/test-endpoint*' => Http::response(['success' => true], 200),
        ]);

        $response = $discordApi->get('test-endpoint');

        $this->assertEquals(200, $response->status());
        Http::assertSent(function ($request) {
            return ! $request->hasHeader('Authorization');
        });
    }

    public function test_http_client_creates_fresh_instance_per_request()
    {
        Http::fake([
            'https://discord.com/api/v10/test-endpoint*' => Http::response(['success' => true], 200),
        ]);

        $response1 = $this->discordApi->get('test-endpoint');
        $response2 = $this->discordApi->get('test-endpoint');

        $this->assertEquals(200, $response1->status());
        $this->assertEquals(200, $response2->status());
    }

    public function test_audit_reason_is_properly_formatted()
    {
        $reflection = new \ReflectionClass($this->discordApi);
        $method = $reflection->getMethod('formatAuditReason');
        $method->setAccessible(true);

        // Test trimming
        $this->assertEquals('test', $method->invoke($this->discordApi, '  test  '));

        // Test length limit
        $longReason = str_repeat('a', 600);
        $formatted = $method->invoke($this->discordApi, $longReason);
        $this->assertEquals(512, mb_strlen($formatted));

        // Test normal case
        $this->assertEquals('normal reason', $method->invoke($this->discordApi, 'normal reason'));
    }

    public function test_audit_reason_is_sent_in_header()
    {
        Http::fake([
            'https://discord.com/api/v10/test-endpoint*' => Http::response(['success' => true], 200),
        ]);

        $this->discordApi->post('test-endpoint', ['data' => 'test'], [], 'Test reason');

        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Audit-Log-Reason', 'Test reason');
        });
    }

    public function test_audit_reason_is_not_sent_when_not_provided()
    {
        Http::fake([
            'https://discord.com/api/v10/test-endpoint*' => Http::response(['success' => true], 200),
        ]);

        $this->discordApi->post('test-endpoint', ['data' => 'test']);

        Http::assertSent(function ($request) {
            return ! $request->hasHeader('X-Audit-Log-Reason');
        });
    }

    public function test_rate_limit_tracking_with_cache()
    {
        Http::fake([
            'https://discord.com/api/v10/test-endpoint*' => Http::response(
                ['success' => true],
                200,
                [
                    'X-RateLimit-Remaining' => '95',
                    'X-RateLimit-Limit' => '100',
                    'X-RateLimit-Reset' => (time() + 60),
                    'X-RateLimit-Bucket' => 'test-bucket',
                ]
            ),
        ]);

        $this->discordApi->get('test-endpoint');

        $status = $this->discordApi->getRateLimitStatus();
        $this->assertArrayHasKey('test-bucket', $status);
        $this->assertEquals(95, $status['test-bucket']['remaining']);
        $this->assertEquals(100, $status['test-bucket']['limit']);
    }

    public function test_get_bucket_rate_limit_status()
    {
        Http::fake([
            'https://discord.com/api/v10/test-endpoint*' => Http::response(
                ['success' => true],
                200,
                [
                    'X-RateLimit-Remaining' => '90',
                    'X-RateLimit-Limit' => '100',
                    'X-RateLimit-Reset' => (time() + 60),
                    'X-RateLimit-Bucket' => 'test-bucket',
                ]
            ),
        ]);

        $this->discordApi->get('test-endpoint');

        $status = $this->discordApi->getBucketRateLimitStatus('test-bucket');
        $this->assertNotNull($status);
        $this->assertEquals(90, $status['remaining']);
        $this->assertEquals(100, $status['limit']);
        $this->assertEquals(90.0, $status['percentage']);

        // Test non-existent bucket
        $this->assertNull($this->discordApi->getBucketRateLimitStatus('non-existent'));
    }

    public function test_clear_rate_limit_tracking()
    {
        Http::fake([
            'https://discord.com/api/v10/test-endpoint*' => Http::response(
                ['success' => true],
                200,
                [
                    'X-RateLimit-Remaining' => '95',
                    'X-RateLimit-Limit' => '100',
                    'X-RateLimit-Reset' => (time() + 60),
                    'X-RateLimit-Bucket' => 'test-bucket',
                ]
            ),
        ]);

        $this->discordApi->get('test-endpoint');

        // Verify tracking is working
        $status = $this->discordApi->getRateLimitStatus();
        $this->assertArrayHasKey('test-bucket', $status);

        // Clear tracking
        $this->discordApi->clearRateLimitTracking();

        // Verify tracking is cleared
        $status = $this->discordApi->getRateLimitStatus();
        $this->assertEmpty($status);
    }

    public function test_global_rate_limit_handling()
    {
        Http::fake([
            'https://discord.com/api/v10/test-endpoint*' => Http::sequence()
                ->push(['error' => 'global rate limit'], 429, ['X-RateLimit-Global' => 'true', 'Retry-After' => '1'])
                ->push(['success' => true], 200),
        ]);

        $response = $this->discordApi->get('test-endpoint');

        $this->assertEquals(200, $response->status());
        $this->assertEquals(['success' => true], $response->json());
    }
}
