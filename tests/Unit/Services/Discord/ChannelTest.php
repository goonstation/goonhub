<?php

namespace Tests\Unit\Services\Discord;

use App\Services\Discord\Api;
use App\Services\Discord\Channel;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChannelTest extends TestCase
{
    private Api $discordApi;

    private Channel $channelApi;

    private string $channelId = '123456789';

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
        $this->channelApi = new Channel($this->discordApi, $this->channelId);
    }

    public function test_get_channel_id_returns_correct_value()
    {
        $this->assertEquals($this->channelId, $this->channelApi->getChannelId());
    }

    public function test_get_channel_information()
    {
        Http::fake([
            "https://discord.com/api/v10/channels/{$this->channelId}" => Http::response(['id' => $this->channelId], 200),
        ]);
        $response = $this->channelApi->get();
        $this->assertEquals(200, $response->status());
        $this->assertEquals(['id' => $this->channelId], $response->json());
    }

    public function test_update_channel_with_reason()
    {
        Http::fake([
            "https://discord.com/api/v10/channels/{$this->channelId}" => Http::response(['id' => $this->channelId, 'name' => 'Updated'], 200),
        ]);
        $data = ['name' => 'Updated'];
        $response = $this->channelApi->update($data, 'Channel update reason');
        $this->assertEquals(200, $response->status());
        $this->assertEquals(['id' => $this->channelId, 'name' => 'Updated'], $response->json());
        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Audit-Log-Reason', 'Channel update reason');
        });
    }

    public function test_delete_channel_with_reason()
    {
        Http::fake([
            "https://discord.com/api/v10/channels/{$this->channelId}" => Http::response(null, 204),
        ]);
        $response = $this->channelApi->delete('Channel delete reason');
        $this->assertEquals(204, $response->status());
        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Audit-Log-Reason', 'Channel delete reason');
        });
    }

    public function test_send_message_to_channel()
    {
        Http::fake([
            "https://discord.com/api/v10/channels/{$this->channelId}/messages" => Http::response(['id' => 'msg1'], 201),
        ]);
        $data = ['content' => 'Hello, Discord!'];
        $response = $this->channelApi->send($data);
        $this->assertEquals(201, $response->status());
        $this->assertEquals(['id' => 'msg1'], $response->json());
    }

    public function test_get_channel_messages()
    {
        Http::fake([
            "https://discord.com/api/v10/channels/{$this->channelId}/messages*" => Http::response([['id' => 'msg1']], 200),
        ]);
        $response = $this->channelApi->getMessages(['limit' => 10]);
        $this->assertEquals(200, $response->status());
        $this->assertEquals([['id' => 'msg1']], $response->json());
    }

    public function test_create_webhook_for_channel()
    {
        Http::fake([
            "https://discord.com/api/v10/channels/{$this->channelId}/webhooks" => Http::response(['id' => 'webhook1'], 201),
        ]);
        $data = ['name' => 'Test Webhook'];
        $response = $this->channelApi->createWebhook($data);
        $this->assertEquals(201, $response->status());
        $this->assertEquals(['id' => 'webhook1'], $response->json());
    }

    public function test_get_channel_invites()
    {
        Http::fake([
            "https://discord.com/api/v10/channels/{$this->channelId}/invites" => Http::response([['code' => 'invite1']], 200),
        ]);
        $response = $this->channelApi->getInvites();
        $this->assertEquals(200, $response->status());
        $this->assertEquals([['code' => 'invite1']], $response->json());
    }

    public function test_create_channel_invite_with_reason()
    {
        Http::fake([
            "https://discord.com/api/v10/channels/{$this->channelId}/invites" => Http::response(['code' => 'invite2'], 201),
        ]);
        $data = ['max_age' => 3600];
        $response = $this->channelApi->createInvite($data, 'Invite creation reason');
        $this->assertEquals(201, $response->status());
        $this->assertEquals(['code' => 'invite2'], $response->json());
        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Audit-Log-Reason', 'Invite creation reason');
        });
    }

    public function test_messages_fluent_interface()
    {
        Http::fake([
            "https://discord.com/api/v10/channels/{$this->channelId}/messages*" => Http::response([['id' => 'msg1']], 200),
        ]);
        $messages = $this->channelApi->messages();
        $this->assertEquals($this->channelId, $messages->getChannelId());
        $response = $messages->get(['limit' => 10]);
        $this->assertEquals(200, $response->status());
    }

    public function test_webhooks_fluent_interface()
    {
        Http::fake([
            "https://discord.com/api/v10/channels/{$this->channelId}/webhooks" => Http::response([['id' => 'webhook1']], 200),
        ]);
        $webhooks = $this->channelApi->webhooks();
        $this->assertEquals($this->channelId, $webhooks->getChannelId());
        $response = $webhooks->get();
        $this->assertEquals(200, $response->status());
    }

    public function test_invites_fluent_interface()
    {
        Http::fake([
            "https://discord.com/api/v10/channels/{$this->channelId}/invites" => Http::response([['code' => 'invite1']], 200),
        ]);
        $invites = $this->channelApi->invites();
        $this->assertEquals($this->channelId, $invites->getChannelId());
        $response = $invites->get();
        $this->assertEquals(200, $response->status());
    }

    public function test_message_fluent_interface()
    {
        $messageId = 'msg123';
        Http::fake([
            "https://discord.com/api/v10/channels/{$this->channelId}/messages/{$messageId}" => Http::response(['id' => $messageId], 200),
        ]);
        $message = $this->channelApi->message($messageId);
        $this->assertEquals($messageId, $message->getMessageId());
        $this->assertEquals($this->channelId, $message->getChannelId());
        $response = $message->get();
        $this->assertEquals(200, $response->status());
    }

    public function test_error_on_invalid_channel_id()
    {
        $channelApi = new Channel($this->discordApi, ''); // Empty string as invalid channel ID
        Http::fake([
            'https://discord.com/api/v10/channels/*' => Http::response(['message' => '404: Not Found', 'code' => 0], 404),
        ]);
        $this->expectException(RequestException::class);
        try {
            $channelApi->get();
        } catch (RequestException $e) {
            $this->assertEquals(404, $e->response->status());
            throw $e;
        }
    }
}
