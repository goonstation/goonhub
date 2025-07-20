<?php

namespace Tests\Unit\Services\Discord;

use App\Services\Discord\Api;
use App\Services\Discord\User;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Tests\TestCase;

class UserTest extends TestCase
{
    private Api $discordApi;

    private User $userApi;

    private string $userId = '123456789';

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.discord.api', [
            'base_url' => 'https://discord.com/api/v10',
            'bot_token' => 'test-bot-token',
            'timeout' => 30,
            'retry_attempts' => 3,
            'retry_delay' => 1000,
        ]);
        $this->discordApi = new Api;
        $this->userApi = new User($this->discordApi, $this->userId);
    }

    public function test_get_user_id_returns_correct_value()
    {
        $this->assertEquals($this->userId, $this->userApi->getUserId());
    }

    public function test_get_user_id_returns_null_when_not_set()
    {
        $userApi = new User($this->discordApi);
        $this->assertNull($userApi->getUserId());
    }

    public function test_get_user_information()
    {
        Http::fake([
            "https://discord.com/api/v10/users/{$this->userId}" => Http::response([
                'id' => $this->userId,
                'username' => 'testuser',
                'discriminator' => '0000',
            ], 200),
        ]);
        $response = $this->userApi->get();
        $this->assertEquals(200, $response->status());
        $this->assertEquals($this->userId, $response->json('id'));
        $this->assertEquals('testuser', $response->json('username'));
    }

    public function test_get_current_bot_user()
    {
        Http::fake([
            'https://discord.com/api/v10/users/@me' => Http::response([
                'id' => 'bot123',
                'username' => 'TestBot',
                'bot' => true,
            ], 200),
        ]);
        $response = $this->userApi->me();
        $this->assertEquals(200, $response->status());
        $this->assertEquals('bot123', $response->json('id'));
        $this->assertEquals('TestBot', $response->json('username'));
        $this->assertTrue($response->json('bot'));
    }

    public function test_get_throws_exception_when_user_id_not_set()
    {
        $userApi = new User($this->discordApi); // No user ID
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User ID is required for get() operation');
        $userApi->get();
    }

    public function test_error_on_invalid_user_id()
    {
        Http::fake([
            'https://discord.com/api/v10/users/*' => Http::response(['message' => '404: Not Found', 'code' => 0], 404),
        ]);
        $this->expectException(RequestException::class);
        try {
            $this->userApi->get();
        } catch (RequestException $e) {
            $this->assertEquals(404, $e->response->status());
            throw $e;
        }
    }

    public function test_fluent_interface_with_user_id()
    {
        Http::fake([
            "https://discord.com/api/v10/users/{$this->userId}" => Http::response(['id' => $this->userId], 200),
        ]);
        $userApi = $this->discordApi->user($this->userId);
        $this->assertEquals($this->userId, $userApi->getUserId());
        $response = $userApi->get();
        $this->assertEquals(200, $response->status());
    }

    public function test_fluent_interface_without_user_id()
    {
        Http::fake([
            'https://discord.com/api/v10/users/@me' => Http::response(['id' => 'bot123'], 200),
        ]);
        $userApi = $this->discordApi->user();
        $this->assertNull($userApi->getUserId());
        $response = $userApi->me();
        $this->assertEquals(200, $response->status());
    }
}
