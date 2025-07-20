<?php

namespace Tests\Feature;

use App\Facades\DiscordApi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DiscordApiFeatureTest extends TestCase
{
    use RefreshDatabase;

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
    }

    public function test_can_get_user_information()
    {
        Http::fake([
            'discord.com/api/v10/users/123*' => Http::response([
                'id' => '123',
                'username' => 'testuser',
                'discriminator' => '0000',
                'avatar' => 'avatar-hash',
                'bot' => false,
                'system' => false,
                'mfa_enabled' => false,
                'banner' => null,
                'accent_color' => null,
                'locale' => 'en-US',
                'verified' => true,
                'email' => 'test@example.com',
                'flags' => 0,
                'premium_type' => 0,
                'public_flags' => 0,
            ], 200),
        ]);

        $response = DiscordApi::getUser('123');

        $this->assertEquals(200, $response->status());
        $this->assertEquals('testuser', $response->json('username'));
        $this->assertEquals('123', $response->json('id'));
    }

    public function test_can_send_message_to_channel()
    {
        Http::fake([
            'discord.com/api/v10/channels/456/messages*' => Http::response([
                'id' => '789',
                'type' => 0,
                'content' => 'Hello, Discord!',
                'channel_id' => '456',
                'author' => [
                    'id' => 'bot-id',
                    'username' => 'TestBot',
                    'bot' => true,
                ],
                'attachments' => [],
                'embeds' => [],
                'mentions' => [],
                'mention_roles' => [],
                'pinned' => false,
                'mention_everyone' => false,
                'tts' => false,
                'timestamp' => '2023-01-01T00:00:00.000000+00:00',
                'edited_timestamp' => null,
                'flags' => 0,
                'components' => [],
            ], 200),
        ]);

        $messageData = [
            'content' => 'Hello, Discord!',
            'tts' => false,
        ];

        $response = DiscordApi::createMessage('456', $messageData);

        $this->assertEquals(200, $response->status());
        $this->assertEquals('Hello, Discord!', $response->json('content'));
        $this->assertEquals('789', $response->json('id'));
    }

    public function test_can_get_guild_information()
    {
        Http::fake([
            'discord.com/api/v10/guilds/789*' => Http::response([
                'id' => '789',
                'name' => 'Test Guild',
                'icon' => 'guild-icon-hash',
                'icon_hash' => 'guild-icon-hash',
                'splash' => null,
                'discovery_splash' => null,
                'owner' => false,
                'owner_id' => 'owner-id',
                'permissions' => '2147483648',
                'region' => null,
                'afk_channel_id' => null,
                'afk_timeout' => 300,
                'widget_enabled' => false,
                'widget_channel_id' => null,
                'verification_level' => 0,
                'default_message_notifications' => 0,
                'explicit_content_filter' => 0,
                'roles' => [],
                'emojis' => [],
                'features' => [],
                'mfa_level' => 0,
                'application_id' => null,
                'system_channel_id' => null,
                'system_channel_flags' => 0,
                'rules_channel_id' => null,
                'max_presences' => null,
                'max_members' => null,
                'vanity_url_code' => null,
                'description' => null,
                'banner' => null,
                'premium_tier' => 0,
                'premium_subscription_count' => 0,
                'preferred_locale' => 'en-US',
                'public_updates_channel_id' => null,
                'max_video_channel_users' => null,
                'max_stage_video_channel_users' => null,
                'approximate_member_count' => null,
                'approximate_presence_count' => null,
                'welcome_screen' => null,
                'nsfw_level' => 0,
                'stickers' => [],
                'premium_progress_bar_enabled' => false,
            ], 200),
        ]);

        $response = DiscordApi::getGuild('789');

        $this->assertEquals(200, $response->status());
        $this->assertEquals('Test Guild', $response->json('name'));
        $this->assertEquals('789', $response->json('id'));
    }

    public function test_can_get_guild_members()
    {
        Http::fake([
            'discord.com/api/v10/guilds/789/members*' => Http::response([
                [
                    'user' => [
                        'id' => '123',
                        'username' => 'user1',
                        'discriminator' => '0001',
                        'avatar' => 'avatar1',
                        'bot' => false,
                    ],
                    'nick' => null,
                    'avatar' => null,
                    'roles' => ['role1', 'role2'],
                    'joined_at' => '2023-01-01T00:00:00.000000+00:00',
                    'premium_since' => null,
                    'deaf' => false,
                    'mute' => false,
                    'flags' => 0,
                    'pending' => false,
                    'permissions' => '2147483648',
                    'communication_disabled_until' => null,
                ],
                [
                    'user' => [
                        'id' => '456',
                        'username' => 'user2',
                        'discriminator' => '0002',
                        'avatar' => 'avatar2',
                        'bot' => false,
                    ],
                    'nick' => 'Nickname',
                    'avatar' => null,
                    'roles' => ['role1'],
                    'joined_at' => '2023-01-02T00:00:00.000000+00:00',
                    'premium_since' => null,
                    'deaf' => false,
                    'mute' => false,
                    'flags' => 0,
                    'pending' => false,
                    'permissions' => '2147483648',
                    'communication_disabled_until' => null,
                ],
            ], 200),
        ]);

        $response = DiscordApi::getGuildMembers('789');

        $this->assertEquals(200, $response->status());
        $this->assertCount(2, $response->json());
        $this->assertEquals('user1', $response->json('0.user.username'));
        $this->assertEquals('user2', $response->json('1.user.username'));
    }

    public function test_can_create_and_execute_webhook()
    {
        // First, create a webhook
        Http::fake([
            'discord.com/api/v10/channels/456/webhooks*' => Http::response([
                'id' => 'webhook-id',
                'type' => 1,
                'name' => 'Test Webhook',
                'avatar' => null,
                'channel_id' => '456',
                'guild_id' => '789',
                'application_id' => null,
                'token' => 'webhook-token',
                'url' => 'https://discord.com/api/webhooks/webhook-id/webhook-token',
            ], 200),
        ]);

        $webhookData = [
            'name' => 'Test Webhook',
        ];

        $createResponse = DiscordApi::createWebhook('456', $webhookData);

        $this->assertEquals(200, $createResponse->status());
        $this->assertEquals('Test Webhook', $createResponse->json('name'));
        $this->assertEquals('webhook-id', $createResponse->json('id'));

        // Then, execute the webhook
        Http::fake([
            'discord.com/api/v10/webhooks/webhook-id/webhook-token*' => Http::response([
                'id' => 'message-id',
                'type' => 0,
                'content' => 'Webhook message',
                'channel_id' => '456',
                'author' => [
                    'id' => 'webhook-id',
                    'username' => 'Test Webhook',
                    'avatar' => null,
                    'discriminator' => '0000',
                ],
                'attachments' => [],
                'embeds' => [],
                'mentions' => [],
                'mention_roles' => [],
                'pinned' => false,
                'mention_everyone' => false,
                'tts' => false,
                'timestamp' => '2023-01-01T00:00:00.000000+00:00',
                'edited_timestamp' => null,
                'flags' => 0,
                'components' => [],
            ], 200),
        ]);

        $messageData = [
            'content' => 'Webhook message',
            'username' => 'Custom Username',
        ];

        $executeResponse = DiscordApi::executeWebhook('webhook-id', 'webhook-token', $messageData);

        $this->assertEquals(200, $executeResponse->status());
        $this->assertEquals('Webhook message', $executeResponse->json('content'));
    }

    public function test_can_manage_guild_roles()
    {
        // Get guild roles
        Http::fake([
            'discord.com/api/v10/guilds/789/roles*' => Http::response([
                [
                    'id' => 'role1',
                    'name' => 'Admin',
                    'color' => 16711680,
                    'hoist' => true,
                    'icon' => null,
                    'unicode_emoji' => null,
                    'position' => 1,
                    'permissions' => '8',
                    'managed' => false,
                    'mentionable' => true,
                    'flags' => 0,
                ],
                [
                    'id' => 'role2',
                    'name' => 'Member',
                    'color' => 0,
                    'hoist' => false,
                    'icon' => null,
                    'unicode_emoji' => null,
                    'position' => 0,
                    'permissions' => '0',
                    'managed' => false,
                    'mentionable' => false,
                    'flags' => 0,
                ],
            ], 200),
        ]);

        $getRolesResponse = DiscordApi::getGuildRoles('789');

        $this->assertEquals(200, $getRolesResponse->status());
        $this->assertCount(2, $getRolesResponse->json());

        // Create a new role
        Http::fake([
            'discord.com/api/v10/guilds/789/roles*' => Http::response([
                'id' => 'role3',
                'name' => 'Moderator',
                'color' => 16776960,
                'hoist' => false,
                'icon' => null,
                'unicode_emoji' => null,
                'position' => 2,
                'permissions' => '2048',
                'managed' => false,
                'mentionable' => false,
                'flags' => 0,
            ], 200),
        ]);

        $newRoleData = [
            'name' => 'Moderator',
            'color' => 16776960,
            'permissions' => '2048',
        ];

        $createRoleResponse = DiscordApi::createGuildRole('789', $newRoleData);

        $this->assertEquals(200, $createRoleResponse->status());
        $this->assertEquals('Moderator', $createRoleResponse->json('name'));
        $this->assertEquals('role3', $createRoleResponse->json('id'));
    }

    public function test_can_manage_member_roles()
    {
        // Add role to member
        Http::fake([
            'discord.com/api/v10/guilds/789/members/123/roles/role1*' => Http::response(null, 204),
        ]);

        $addRoleResponse = DiscordApi::addGuildMemberRole('789', '123', 'role1');

        $this->assertEquals(204, $addRoleResponse->status());

        // Remove role from member
        Http::fake([
            'discord.com/api/v10/guilds/789/members/123/roles/role1*' => Http::response(null, 204),
        ]);

        $removeRoleResponse = DiscordApi::removeGuildMemberRole('789', '123', 'role1');

        $this->assertEquals(204, $removeRoleResponse->status());
    }

    public function test_can_manage_reactions()
    {
        // Add reaction
        Http::fake([
            'discord.com/api/v10/channels/456/messages/789/reactions/%F0%9F%98%80/@me*' => Http::response(null, 204),
        ]);

        $addReactionResponse = DiscordApi::createReaction('456', '789', '😀');

        $this->assertEquals(204, $addReactionResponse->status());

        // Remove reaction
        Http::fake([
            'discord.com/api/v10/channels/456/messages/789/reactions/%F0%9F%98%80/@me*' => Http::response(null, 204),
        ]);

        $removeReactionResponse = DiscordApi::deleteReaction('456', '789', '😀');

        $this->assertEquals(204, $removeReactionResponse->status());
    }

    public function test_can_get_current_bot_user()
    {
        Http::fake([
            'discord.com/api/v10/users/@me*' => Http::response([
                'id' => 'bot-id',
                'username' => 'TestBot',
                'discriminator' => '0000',
                'avatar' => 'bot-avatar',
                'bot' => true,
                'system' => false,
                'mfa_enabled' => false,
                'banner' => null,
                'accent_color' => null,
                'locale' => 'en-US',
                'verified' => true,
                'email' => null,
                'flags' => 0,
                'premium_type' => 0,
                'public_flags' => 0,
            ], 200),
        ]);

        $response = DiscordApi::getCurrentUser();

        $this->assertEquals(200, $response->status());
        $this->assertEquals('TestBot', $response->json('username'));
        $this->assertEquals('bot-id', $response->json('id'));
        $this->assertTrue($response->json('bot'));
    }

    public function test_can_handle_rate_limiting()
    {
        Http::fake([
            'discord.com/api/v10/test-endpoint*' => Http::sequence()
                ->push(['error' => 'rate limited'], 429, [
                    'X-RateLimit-Limit' => '5',
                    'X-RateLimit-Remaining' => '0',
                    'X-RateLimit-Reset' => time() + 60,
                ])
                ->push(['success' => true], 200),
        ]);

        $response = DiscordApi::get('test-endpoint');

        $this->assertEquals(200, $response->status());
        $this->assertEquals(['success' => true], $response->json());
    }

    public function test_can_use_facade_without_bot_token()
    {
        Config::set('services.discord.api.bot_token', null);

        $this->assertFalse(DiscordApi::hasBotToken());
        $this->assertEquals('https://discord.com/api/v10', DiscordApi::getBaseUrl());
    }

    public function test_can_delete_message_with_audit_reason()
    {
        Http::fake([
            'discord.com/api/v10/channels/456/messages/789*' => Http::response(null, 204),
        ]);

        $response = DiscordApi::deleteMessage('456', '789', 'Message contained inappropriate content');

        $this->assertEquals(204, $response->status());

        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Audit-Log-Reason', 'Message%20contained%20inappropriate%20content');
        });
    }

    public function test_can_create_role_with_audit_reason()
    {
        Http::fake([
            'discord.com/api/v10/guilds/789/roles*' => Http::response([
                'id' => 'role1',
                'name' => 'Event Organizer',
                'color' => 16776960,
                'hoist' => false,
                'position' => 1,
                'permissions' => '2048',
                'managed' => false,
                'mentionable' => false,
            ], 200),
        ]);

        $roleData = [
            'name' => 'Event Organizer',
            'color' => 16776960,
            'permissions' => '2048',
        ];

        $response = DiscordApi::createGuildRole('789', $roleData, 'New role for event management');

        $this->assertEquals(200, $response->status());
        $this->assertEquals('Event Organizer', $response->json('name'));

        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Audit-Log-Reason', 'New%20role%20for%20event%20management');
        });
    }

    public function test_can_ban_member_with_audit_reason()
    {
        Http::fake([
            'discord.com/api/v10/guilds/789/bans/123*' => Http::response(null, 204),
        ]);

        $banData = [
            'delete_message_days' => 1,
            'reason' => 'Spam and harassment',
        ];

        $response = DiscordApi::banGuildMember('789', '123', $banData, 'Repeated violations of community guidelines');

        $this->assertEquals(204, $response->status());

        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Audit-Log-Reason', 'Repeated%20violations%20of%20community%20guidelines');
        });
    }

    public function test_can_kick_member_with_audit_reason()
    {
        Http::fake([
            'discord.com/api/v10/guilds/789/members/123*' => Http::response(null, 204),
        ]);

        $response = DiscordApi::kickGuildMember('789', '123', 'Temporary removal for inappropriate behavior');

        $this->assertEquals(204, $response->status());

        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Audit-Log-Reason', 'Temporary%20removal%20for%20inappropriate%20behavior');
        });
    }

    public function test_can_add_role_to_member_with_audit_reason()
    {
        Http::fake([
            'discord.com/api/v10/guilds/789/members/123/roles/role1*' => Http::response(null, 204),
        ]);

        $response = DiscordApi::addGuildMemberRole('789', '123', 'role1', 'Promoted for excellent community contributions');

        $this->assertEquals(204, $response->status());

        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Audit-Log-Reason', 'Promoted%20for%20excellent%20community%20contributions');
        });
    }

    public function test_can_remove_role_from_member_with_audit_reason()
    {
        Http::fake([
            'discord.com/api/v10/guilds/789/members/123/roles/role1*' => Http::response(null, 204),
        ]);

        $response = DiscordApi::removeGuildMemberRole('789', '123', 'role1', 'Role removed due to inactivity');

        $this->assertEquals(204, $response->status());

        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Audit-Log-Reason', 'Role%20removed%20due%20to%20inactivity');
        });
    }

    public function test_can_delete_channel_with_audit_reason()
    {
        Http::fake([
            'discord.com/api/v10/channels/456*' => Http::response(null, 200),
        ]);

        $response = DiscordApi::deleteChannel('456', 'Channel cleanup - no longer needed');

        $this->assertEquals(200, $response->status());

        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Audit-Log-Reason', 'Channel%20cleanup%20-%20no%20longer%20needed');
        });
    }

    public function test_audit_reason_is_properly_url_encoded()
    {
        Http::fake([
            'discord.com/api/v10/guilds/789/roles*' => Http::response(['id' => 'role1'], 200),
        ]);

        $response = DiscordApi::createGuildRole('789', ['name' => 'Test'], 'Reason with special chars: !@#$%^&*()');

        $this->assertEquals(200, $response->status());

        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Audit-Log-Reason', 'Reason%20with%20special%20chars%3A%20%21%40%23%24%25%5E%26%2A%28%29');
        });
    }

    public function test_audit_reason_is_truncated_to_512_characters()
    {
        Http::fake([
            'discord.com/api/v10/guilds/789/roles*' => Http::response(['id' => 'role1'], 200),
        ]);

        $longReason = str_repeat('a', 600);
        $response = DiscordApi::createGuildRole('789', ['name' => 'Test'], $longReason);

        $this->assertEquals(200, $response->status());

        Http::assertSent(function ($request) {
            $headerValue = $request->header('X-Audit-Log-Reason')[0];
            $decodedValue = urldecode($headerValue);

            return mb_strlen($decodedValue) === 512;
        });
    }
}
