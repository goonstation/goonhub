<?php

namespace Tests\Unit\Services\Discord;

use App\Services\Discord\Api;
use App\Services\Discord\Guild;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GuildTest extends TestCase
{
    private Api $discordApi;

    private Guild $guildApi;

    private string $guildId = '123456789';

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.discord.api', [
            'base_url' => 'https://discord.com/api/v10',
            'bot_token' => 'test-bot-token',
            'timeout' => 30,
            'retry_attempts' => 3,
            'retry_delay' => 1000,
            'guild_id' => $this->guildId,
        ]);
        $this->discordApi = new Api;
        $this->guildApi = new Guild($this->discordApi, $this->guildId);
    }

    public function test_get_guild_id_returns_correct_value()
    {
        $this->assertEquals($this->guildId, $this->guildApi->getGuildId());
    }

    public function test_get_guild_information()
    {
        Http::fake([
            "https://discord.com/api/v10/guilds/{$this->guildId}" => Http::response(['id' => $this->guildId], 200),
        ]);
        $response = $this->guildApi->get();
        $this->assertEquals(200, $response->status());
        $this->assertEquals(['id' => $this->guildId], $response->json());
    }

    public function test_get_guild_members()
    {
        Http::fake([
            "https://discord.com/api/v10/guilds/{$this->guildId}/members*" => Http::response([['user' => ['id' => '1']]], 200),
        ]);
        $response = $this->guildApi->members(['limit' => 1]);
        $this->assertEquals(200, $response->status());
        $this->assertEquals([['user' => ['id' => '1']]], $response->json());
    }

    public function test_get_specific_guild_member()
    {
        Http::fake([
            "https://discord.com/api/v10/guilds/{$this->guildId}/members/42" => Http::response(['user' => ['id' => '42']], 200),
        ]);
        $response = $this->guildApi->member('42');
        $this->assertEquals(200, $response->status());
        $this->assertEquals(['user' => ['id' => '42']], $response->json());
    }

    public function test_get_guild_roles()
    {
        Http::fake([
            "https://discord.com/api/v10/guilds/{$this->guildId}/roles" => Http::response([['id' => 'role1']], 200),
        ]);
        $response = $this->guildApi->roles();
        $this->assertEquals(200, $response->status());
        $this->assertEquals([['id' => 'role1']], $response->json());
    }

    public function test_create_guild_role_with_reason()
    {
        Http::fake([
            "https://discord.com/api/v10/guilds/{$this->guildId}/roles" => Http::response(['id' => 'role2'], 201),
        ]);
        $data = ['name' => 'TestRole'];
        $response = $this->guildApi->createRole($data, 'Role creation reason');
        $this->assertEquals(201, $response->status());
        $this->assertEquals(['id' => 'role2'], $response->json());
        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Audit-Log-Reason', 'Role creation reason');
        });
    }

    public function test_update_guild_role_with_reason()
    {
        Http::fake([
            "https://discord.com/api/v10/guilds/{$this->guildId}/roles/role2" => Http::response(['id' => 'role2', 'name' => 'Updated'], 200),
        ]);
        $data = ['name' => 'Updated'];
        $response = $this->guildApi->updateRole('role2', $data, 'Role update reason');
        $this->assertEquals(200, $response->status());
        $this->assertEquals(['id' => 'role2', 'name' => 'Updated'], $response->json());
        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Audit-Log-Reason', 'Role update reason');
        });
    }

    public function test_delete_guild_role_with_reason()
    {
        Http::fake([
            "https://discord.com/api/v10/guilds/{$this->guildId}/roles/role3" => Http::response(null, 204),
        ]);
        $response = $this->guildApi->deleteRole('role3', 'Role delete reason');
        $this->assertEquals(204, $response->status());
        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Audit-Log-Reason', 'Role delete reason');
        });
    }

    public function test_add_and_remove_member_role()
    {
        Http::fake([
            "https://discord.com/api/v10/guilds/{$this->guildId}/members/42/roles/role1" => Http::response(null, 204),
        ]);
        $add = $this->guildApi->addMemberRole('42', 'role1', 'Add role reason');
        $this->assertEquals(204, $add->status());
        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Audit-Log-Reason', 'Add role reason');
        });
        $remove = $this->guildApi->removeMemberRole('42', 'role1', 'Remove role reason');
        $this->assertEquals(204, $remove->status());
        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Audit-Log-Reason', 'Remove role reason');
        });
    }

    public function test_get_guild_channels()
    {
        Http::fake([
            "https://discord.com/api/v10/guilds/{$this->guildId}/channels" => Http::response([['id' => 'chan1']], 200),
        ]);
        $response = $this->guildApi->channels();
        $this->assertEquals(200, $response->status());
        $this->assertEquals([['id' => 'chan1']], $response->json());
    }

    public function test_create_guild_channel_with_reason()
    {
        Http::fake([
            "https://discord.com/api/v10/guilds/{$this->guildId}/channels" => Http::response(['id' => 'chan2'], 201),
        ]);
        $data = ['name' => 'new-channel'];
        $response = $this->guildApi->createChannel($data, 'Channel creation reason');
        $this->assertEquals(201, $response->status());
        $this->assertEquals(['id' => 'chan2'], $response->json());
        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Audit-Log-Reason', 'Channel creation reason');
        });
    }

    public function test_ban_and_unban_member()
    {
        Http::fake([
            "https://discord.com/api/v10/guilds/{$this->guildId}/bans/42" => Http::response(null, 204),
        ]);
        $ban = $this->guildApi->banMember('42', ['delete_message_days' => 7], 'Ban reason');
        $this->assertEquals(204, $ban->status());
        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Audit-Log-Reason', 'Ban reason');
        });
        $unban = $this->guildApi->unbanMember('42', 'Unban reason');
        $this->assertEquals(204, $unban->status());
        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Audit-Log-Reason', 'Unban reason');
        });
    }

    public function test_kick_and_update_member()
    {
        Http::fake([
            "https://discord.com/api/v10/guilds/{$this->guildId}/members/42" => Http::sequence()
                ->push(null, 204)
                ->push(['nick' => 'Updated'], 200),
        ]);
        $kick = $this->guildApi->kickMember('42', 'Kick reason');
        $this->assertEquals(204, $kick->status());
        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Audit-Log-Reason', 'Kick reason');
        });
        $update = $this->guildApi->updateMember('42', ['nick' => 'Updated'], 'Update reason');
        $this->assertEquals(200, $update->status());
        $this->assertEquals(['nick' => 'Updated'], $update->json());
        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Audit-Log-Reason', 'Update reason');
        });
    }

    public function test_emojis_and_stickers()
    {
        // Test emojis()
        Http::fake([
            "https://discord.com/api/v10/guilds/{$this->guildId}/emojis" => Http::response([['id' => 'emoji1']], 200),
        ]);
        $emojis = $this->guildApi->emojis();
        $this->assertEquals(200, $emojis->status());
        // Test emoji()
        Http::fake([
            "https://discord.com/api/v10/guilds/{$this->guildId}/emojis/emoji1" => Http::response(['id' => 'emoji1'], 200),
        ]);
        $emoji = $this->guildApi->emoji('emoji1');
        $this->assertEquals(200, $emoji->status());
    }

    public function test_error_on_invalid_guild_id()
    {
        $guildApi = new Guild($this->discordApi, ''); // Empty string as invalid guild ID
        Http::fake([
            'https://discord.com/api/v10/guilds/*' => Http::response(['message' => '404: Not Found', 'code' => 0], 404),
        ]);
        $this->expectException(RequestException::class);
        try {
            $guildApi->get();
        } catch (RequestException $e) {
            $this->assertEquals(404, $e->response->status());
            throw $e;
        }
    }

    public function test_edge_cases_empty_arrays_and_nulls()
    {
        Http::fake([
            "https://discord.com/api/v10/guilds/{$this->guildId}/members*" => Http::response([], 200),
        ]);
        $response = $this->guildApi->members([]);
        $this->assertEquals([], $response->json());
    }

    public function test_bans_and_ban_methods()
    {
        Http::fake([
            "https://discord.com/api/v10/guilds/{$this->guildId}/bans/42" => Http::response(['user' => ['id' => '42']], 200),
            "https://discord.com/api/v10/guilds/{$this->guildId}/bans" => Http::response([['user' => ['id' => '1']]], 200),
        ]);
        $bans = $this->guildApi->bans();
        $this->assertEquals(200, $bans->status());
        $this->assertEquals([['user' => ['id' => '1']]], $bans->json());
        $ban = $this->guildApi->ban('42');
        $this->assertEquals(200, $ban->status());
        $this->assertEquals(['user' => ['id' => '42']], $ban->json());
    }

    public function test_emoji_crud_methods()
    {
        Http::fake([
            "https://discord.com/api/v10/guilds/{$this->guildId}/emojis" => Http::sequence()
                ->push([['id' => 'emoji1']], 200)
                ->push(['id' => 'emoji2'], 201),
            "https://discord.com/api/v10/guilds/{$this->guildId}/emojis/emoji1" => Http::response(['id' => 'emoji1'], 200),
        ]);
        $this->assertEquals(200, $this->guildApi->emojis()->status());
        $create = $this->guildApi->createEmoji(['name' => 'emoji2'], 'reason');
        $this->assertEquals(201, $create->status());
        $this->assertEquals(['id' => 'emoji2'], $create->json());
        $update = $this->guildApi->updateEmoji('emoji1', ['name' => 'updated'], 'reason');
        $this->assertEquals(200, $update->status());
        $this->assertEquals(['id' => 'emoji1'], $update->json());
        $delete = $this->guildApi->deleteEmoji('emoji1', 'reason');
        $this->assertTrue(in_array($delete->status(), [200, 204]));
    }

    public function test_invites_voice_regions_integrations_widget()
    {
        Http::fake([
            "https://discord.com/api/v10/guilds/{$this->guildId}/invites" => Http::response([['code' => 'abc']], 200),
            "https://discord.com/api/v10/guilds/{$this->guildId}/regions" => Http::response([['id' => 'region1']], 200),
            "https://discord.com/api/v10/guilds/{$this->guildId}/integrations" => Http::response([['id' => 'int1']], 200),
            "https://discord.com/api/v10/guilds/{$this->guildId}/integrations/int1" => Http::response(null, 204),
            "https://discord.com/api/v10/guilds/{$this->guildId}/widget" => Http::response(['enabled' => true], 200),
            "https://discord.com/api/v10/guilds/{$this->guildId}/widget.json" => Http::response(['id' => 'widget'], 200),
            "https://discord.com/api/v10/guilds/{$this->guildId}/widget.png*" => Http::response('PNGDATA', 200),
        ]);
        $this->assertEquals(200, $this->guildApi->invites()->status());
        $this->assertEquals(200, $this->guildApi->voiceRegions()->status());
        $this->assertEquals(200, $this->guildApi->integrations()->status());
        $this->assertEquals(204, $this->guildApi->deleteIntegration('int1', 'reason')->status());
        $this->assertEquals(200, $this->guildApi->widget()->status());
        $this->assertEquals(200, $this->guildApi->widgetJson()->status());
        $this->assertEquals(200, $this->guildApi->widgetPng()->status());
    }

    public function test_misc_guild_methods()
    {
        Http::fake([
            "https://discord.com/api/v10/guilds/{$this->guildId}/vanity-url" => Http::response(['code' => 'vanity'], 200),
            "https://discord.com/api/v10/guilds/{$this->guildId}/welcome-screen" => Http::response(['description' => 'Welcome!'], 200),
            "https://discord.com/api/v10/guilds/{$this->guildId}/audit-logs*" => Http::response(['audit_log_entries' => []], 200),
            "https://discord.com/api/v10/guilds/{$this->guildId}/templates" => Http::sequence()
                ->push([['code' => 'tmpl']], 200)
                ->push(['code' => 'tmpl'], 201),
            "https://discord.com/api/v10/guilds/{$this->guildId}/templates/tmpl" => Http::response(['code' => 'tmpl'], 200),
        ]);
        $this->assertEquals(200, $this->guildApi->vanityUrl()->status());
        $this->assertEquals(200, $this->guildApi->welcomeScreen()->status());
        $this->assertEquals(200, $this->guildApi->auditLog()->status());
        $this->assertEquals(200, $this->guildApi->templates()->status());
        $this->assertEquals(201, $this->guildApi->createTemplate(['name' => 'Test'])->status());
        $this->assertEquals(200, $this->guildApi->syncTemplate('tmpl')->status());
        $this->assertEquals(200, $this->guildApi->updateTemplate('tmpl', ['name' => 'Updated'])->status());
        $this->assertEquals(200, $this->guildApi->deleteTemplate('tmpl')->status());
    }

    public function test_scheduled_events_methods()
    {
        Http::fake([
            "https://discord.com/api/v10/guilds/{$this->guildId}/scheduled-events" => Http::sequence()
                ->push([['id' => 'evt1']], 200)
                ->push(['id' => 'evt2'], 201),
            "https://discord.com/api/v10/guilds/{$this->guildId}/scheduled-events/evt1" => Http::response(['id' => 'evt1'], 200),
            "https://discord.com/api/v10/guilds/{$this->guildId}/scheduled-events/evt1/users*" => Http::response([['user' => ['id' => '1']]], 200),
        ]);
        $this->assertEquals(200, $this->guildApi->scheduledEvents()->status());
        $this->assertEquals(201, $this->guildApi->createScheduledEvent(['name' => 'Event'], 'reason')->status());
        $this->assertEquals(200, $this->guildApi->scheduledEvent('evt1')->status());
        $this->assertEquals(200, $this->guildApi->updateScheduledEvent('evt1', ['name' => 'Updated'], 'reason')->status());
        $this->assertEquals(200, $this->guildApi->deleteScheduledEvent('evt1', 'reason')->status());
        $this->assertEquals(200, $this->guildApi->scheduledEventUsers('evt1')->status());
    }

    public function test_sticker_methods()
    {
        Http::fake([
            "https://discord.com/api/v10/guilds/{$this->guildId}/stickers" => Http::sequence()
                ->push([['id' => 'sticker1']], 200)
                ->push(['id' => 'sticker2'], 201),
            "https://discord.com/api/v10/guilds/{$this->guildId}/stickers/sticker1" => Http::response(['id' => 'sticker1'], 200),
        ]);
        $this->assertEquals(200, $this->guildApi->stickers()->status());
        $this->assertEquals(201, $this->guildApi->createSticker(['name' => 'sticker2'], 'reason')->status());
        $this->assertEquals(200, $this->guildApi->updateSticker('sticker1', ['name' => 'updated'], 'reason')->status());
        $this->assertEquals(200, $this->guildApi->deleteSticker('sticker1', 'reason')->status());
        $this->assertEquals(200, $this->guildApi->sticker('sticker1')->status());
    }

    public function test_preview_prune_voice_methods()
    {
        Http::fake([
            "https://discord.com/api/v10/guilds/{$this->guildId}/preview" => Http::response(['id' => 'preview'], 200),
            "https://discord.com/api/v10/guilds/{$this->guildId}/prune*" => Http::response(['pruned' => 5], 200),
            "https://discord.com/api/v10/guilds/{$this->guildId}/voice-states" => Http::response([['user_id' => '1']], 200),
            "https://discord.com/api/v10/guilds/{$this->guildId}/voice-states/42" => Http::response(['user_id' => '42'], 200),
        ]);
        $this->assertEquals(200, $this->guildApi->preview()->status());
        $this->assertEquals(200, $this->guildApi->pruneCount()->status());
        $this->assertEquals(200, $this->guildApi->beginPrune(['days' => 7], 'reason')->status());
        $this->assertEquals(200, $this->guildApi->voiceStates()->status());
        $this->assertEquals(200, $this->guildApi->voiceState('42')->status());
        $this->assertEquals(200, $this->guildApi->updateUserVoiceState('42', ['channel_id' => 'chan'], 'reason')->status());
    }
}
