<?php

namespace Tests\Unit\Services\Discord;

use App\Services\Discord\Api;
use App\Services\Discord\Message;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MessageTest extends TestCase
{
    private Api $discordApi;

    private Message $messageApi;

    private string $channelId = '123456789';

    private string $messageId = '987654321';

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
        $this->messageApi = new Message($this->discordApi, $this->channelId, $this->messageId);
    }

    public function test_get_message_id_returns_correct_value()
    {
        $this->assertEquals($this->messageId, $this->messageApi->getMessageId());
    }

    public function test_get_channel_id_returns_correct_value()
    {
        $this->assertEquals($this->channelId, $this->messageApi->getChannelId());
    }

    public function test_get_message_information()
    {
        Http::fake([
            "https://discord.com/api/v10/channels/{$this->channelId}/messages/{$this->messageId}" => Http::response(['id' => $this->messageId], 200),
        ]);
        $response = $this->messageApi->get();
        $this->assertEquals(200, $response->status());
        $this->assertEquals(['id' => $this->messageId], $response->json());
    }

    public function test_update_message()
    {
        Http::fake([
            "https://discord.com/api/v10/channels/{$this->channelId}/messages/{$this->messageId}" => Http::response(['id' => $this->messageId, 'content' => 'Updated'], 200),
        ]);
        $data = ['content' => 'Updated'];
        $response = $this->messageApi->update($data);
        $this->assertEquals(200, $response->status());
        $this->assertEquals(['id' => $this->messageId, 'content' => 'Updated'], $response->json());
    }

    public function test_delete_message_with_reason()
    {
        Http::fake([
            "https://discord.com/api/v10/channels/{$this->channelId}/messages/{$this->messageId}" => Http::response(null, 204),
        ]);
        $response = $this->messageApi->delete('Message delete reason');
        $this->assertEquals(204, $response->status());
        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Audit-Log-Reason', 'Message delete reason');
        });
    }

    public function test_add_reaction_to_message()
    {
        Http::fake([
            "https://discord.com/api/v10/channels/{$this->channelId}/messages/{$this->messageId}/reactions/%F0%9F%98%80/@me" => Http::response(null, 204),
        ]);
        $response = $this->messageApi->addReaction('😀');
        $this->assertEquals(204, $response->status());
    }

    public function test_remove_reaction_from_message()
    {
        Http::fake([
            "https://discord.com/api/v10/channels/{$this->channelId}/messages/{$this->messageId}/reactions/%F0%9F%98%80/@me" => Http::response(null, 204),
        ]);
        $response = $this->messageApi->removeReaction('😀');
        $this->assertEquals(204, $response->status());
    }

    public function test_get_reactions_for_message()
    {
        Http::fake([
            "https://discord.com/api/v10/channels/{$this->channelId}/messages/{$this->messageId}/reactions/%F0%9F%98%80*" => Http::response([['user' => ['id' => '1']]], 200),
        ]);
        $response = $this->messageApi->getReactions('😀', ['limit' => 10]);
        $this->assertEquals(200, $response->status());
        $this->assertEquals([['user' => ['id' => '1']]], $response->json());
    }

    public function test_delete_all_reactions_from_message()
    {
        Http::fake([
            "https://discord.com/api/v10/channels/{$this->channelId}/messages/{$this->messageId}/reactions" => Http::response(null, 204),
        ]);
        $response = $this->messageApi->deleteAllReactions();
        $this->assertEquals(204, $response->status());
    }

    public function test_delete_all_reactions_for_emoji_from_message()
    {
        Http::fake([
            "https://discord.com/api/v10/channels/{$this->channelId}/messages/{$this->messageId}/reactions/%F0%9F%98%80" => Http::response(null, 204),
        ]);
        $response = $this->messageApi->deleteAllReactionsForEmoji('😀');
        $this->assertEquals(204, $response->status());
    }

    public function test_reactions_fluent_interface()
    {
        Http::fake([
            "https://discord.com/api/v10/channels/{$this->channelId}/messages/{$this->messageId}/reactions/%F0%9F%98%80/@me" => Http::response(null, 204),
        ]);
        $reactions = $this->messageApi->reactions();
        $this->assertEquals($this->channelId, $reactions->getChannelId());
        $this->assertEquals($this->messageId, $reactions->getMessageId());
        $response = $reactions->add('😀');
        $this->assertEquals(204, $response->status());
    }

    public function test_error_on_invalid_message_id()
    {
        $messageApi = new Message($this->discordApi, $this->channelId, ''); // Empty string as invalid message ID
        Http::fake([
            'https://discord.com/api/v10/channels/*' => Http::response(['message' => '404: Not Found', 'code' => 0], 404),
        ]);
        $this->expectException(RequestException::class);
        try {
            $messageApi->get();
        } catch (RequestException $e) {
            $this->assertEquals(404, $e->response->status());
            throw $e;
        }
    }
}
