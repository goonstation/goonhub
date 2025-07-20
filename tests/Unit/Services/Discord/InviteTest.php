<?php

namespace Tests\Unit\Services\Discord;

use App\Services\Discord\Api;
use App\Services\Discord\Invite;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InviteTest extends TestCase
{
    private Api $discordApi;

    private Invite $inviteApi;

    private string $inviteCode = 'abc123';

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
        $this->inviteApi = new Invite($this->discordApi, $this->inviteCode);
    }

    public function test_get_invite_code_returns_correct_value()
    {
        $this->assertEquals($this->inviteCode, $this->inviteApi->getInviteCode());
    }

    public function test_get_invite_information()
    {
        Http::fake([
            "https://discord.com/api/v10/invites/{$this->inviteCode}" => Http::response([
                'code' => $this->inviteCode,
                'guild' => ['id' => 'guild123'],
                'channel' => ['id' => 'channel123'],
                'inviter' => ['id' => 'user123'],
            ], 200),
        ]);
        $response = $this->inviteApi->get();
        $this->assertEquals(200, $response->status());
        $this->assertEquals($this->inviteCode, $response->json('code'));
        $this->assertEquals('guild123', $response->json('guild.id'));
    }

    public function test_get_invite_with_query_parameters()
    {
        Http::fake([
            "https://discord.com/api/v10/invites/{$this->inviteCode}*" => Http::response([
                'code' => $this->inviteCode,
                'approximate_member_count' => 100,
                'approximate_presence_count' => 50,
            ], 200),
        ]);
        $query = ['with_counts' => true];
        $response = $this->inviteApi->get($query);
        $this->assertEquals(200, $response->status());
        $this->assertEquals(100, $response->json('approximate_member_count'));
        $this->assertEquals(50, $response->json('approximate_presence_count'));
    }

    public function test_delete_invite_with_reason()
    {
        Http::fake([
            "https://discord.com/api/v10/invites/{$this->inviteCode}" => Http::response(null, 204),
        ]);
        $response = $this->inviteApi->delete('Invite expired');
        $this->assertEquals(204, $response->status());
        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Audit-Log-Reason', 'Invite expired');
        });
    }

    public function test_delete_invite_without_reason()
    {
        Http::fake([
            "https://discord.com/api/v10/invites/{$this->inviteCode}" => Http::response(null, 204),
        ]);
        $response = $this->inviteApi->delete();
        $this->assertEquals(204, $response->status());
    }

    public function test_error_on_invalid_invite_code()
    {
        Http::fake([
            'https://discord.com/api/v10/invites/*' => Http::response(['message' => '404: Not Found', 'code' => 0], 404),
        ]);
        $this->expectException(RequestException::class);
        try {
            $this->inviteApi->get();
        } catch (RequestException $e) {
            $this->assertEquals(404, $e->response->status());
            throw $e;
        }
    }

    public function test_fluent_interface()
    {
        Http::fake([
            "https://discord.com/api/v10/invites/{$this->inviteCode}" => Http::response(['code' => $this->inviteCode], 200),
        ]);
        $inviteApi = $this->discordApi->invite($this->inviteCode);
        $this->assertEquals($this->inviteCode, $inviteApi->getInviteCode());
        $response = $inviteApi->get();
        $this->assertEquals(200, $response->status());
    }
}
