<?php

namespace Tests\Unit\Services\Discord;

use App\Services\Discord\Api;
use App\Services\Discord\Application;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApplicationTest extends TestCase
{
    private Api $discordApi;

    private Application $applicationApi;

    private string $applicationId = 'app123';

    private string $guildId = 'guild456';

    private string $commandId = 'cmd789';

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
        $this->applicationApi = new Application($this->discordApi, $this->applicationId);
    }

    public function test_get_application_id_returns_correct_value()
    {
        $this->assertEquals($this->applicationId, $this->applicationApi->getApplicationId());
    }

    public function test_get_application_commands()
    {
        Http::fake([
            "https://discord.com/api/v10/applications/{$this->applicationId}/commands" => Http::response([
                ['id' => $this->commandId, 'name' => 'ping'],
            ], 200),
        ]);
        $response = $this->applicationApi->commands();
        $this->assertEquals(200, $response->status());
        $this->assertEquals($this->commandId, $response->json('0.id'));
        $this->assertEquals('ping', $response->json('0.name'));
    }

    public function test_create_application_command()
    {
        Http::fake([
            "https://discord.com/api/v10/applications/{$this->applicationId}/commands" => Http::response([
                'id' => $this->commandId,
                'name' => 'ping',
                'description' => 'Ping command',
            ], 201),
        ]);
        $data = ['name' => 'ping', 'description' => 'Ping command', 'type' => 1];
        $response = $this->applicationApi->createCommand($data);
        $this->assertEquals(201, $response->status());
        $this->assertEquals('ping', $response->json('name'));
    }

    public function test_get_specific_application_command()
    {
        Http::fake([
            "https://discord.com/api/v10/applications/{$this->applicationId}/commands/{$this->commandId}" => Http::response([
                'id' => $this->commandId,
                'name' => 'ping',
            ], 200),
        ]);
        $response = $this->applicationApi->command($this->commandId);
        $this->assertEquals(200, $response->status());
        $this->assertEquals($this->commandId, $response->json('id'));
    }

    public function test_update_application_command()
    {
        Http::fake([
            "https://discord.com/api/v10/applications/{$this->applicationId}/commands/{$this->commandId}" => Http::response([
                'id' => $this->commandId,
                'name' => 'ping',
                'description' => 'Updated ping command',
            ], 200),
        ]);
        $data = ['description' => 'Updated ping command'];
        $response = $this->applicationApi->updateCommand($this->commandId, $data);
        $this->assertEquals(200, $response->status());
        $this->assertEquals('Updated ping command', $response->json('description'));
    }

    public function test_delete_application_command()
    {
        Http::fake([
            "https://discord.com/api/v10/applications/{$this->applicationId}/commands/{$this->commandId}" => Http::response(null, 204),
        ]);
        $response = $this->applicationApi->deleteCommand($this->commandId);
        $this->assertEquals(204, $response->status());
    }

    public function test_bulk_overwrite_application_commands()
    {
        Http::fake([
            "https://discord.com/api/v10/applications/{$this->applicationId}/commands" => Http::response([
                ['id' => $this->commandId, 'name' => 'ping'],
                ['id' => 'cmd456', 'name' => 'echo'],
            ], 200),
        ]);
        $commands = [
            ['name' => 'ping', 'description' => 'Ping command', 'type' => 1],
            ['name' => 'echo', 'description' => 'Echo command', 'type' => 1],
        ];
        $response = $this->applicationApi->bulkOverwriteCommands($commands);
        $this->assertEquals(200, $response->status());
        $this->assertCount(2, $response->json());
    }

    public function test_get_guild_application_commands()
    {
        Http::fake([
            "https://discord.com/api/v10/applications/{$this->applicationId}/guilds/{$this->guildId}/commands" => Http::response([
                ['id' => $this->commandId, 'name' => 'ping'],
            ], 200),
        ]);
        $response = $this->applicationApi->guildCommands($this->guildId);
        $this->assertEquals(200, $response->status());
        $this->assertEquals($this->commandId, $response->json('0.id'));
    }

    public function test_create_guild_application_command()
    {
        Http::fake([
            "https://discord.com/api/v10/applications/{$this->applicationId}/guilds/{$this->guildId}/commands" => Http::response([
                'id' => $this->commandId,
                'name' => 'ping',
                'guild_id' => $this->guildId,
            ], 201),
        ]);
        $data = ['name' => 'ping', 'description' => 'Ping command', 'type' => 1];
        $response = $this->applicationApi->createGuildCommand($this->guildId, $data);
        $this->assertEquals(201, $response->status());
        $this->assertEquals($this->guildId, $response->json('guild_id'));
    }

    public function test_get_guild_application_command()
    {
        Http::fake([
            "https://discord.com/api/v10/applications/{$this->applicationId}/guilds/{$this->guildId}/commands/{$this->commandId}" => Http::response([
                'id' => $this->commandId,
                'name' => 'ping',
                'guild_id' => $this->guildId,
            ], 200),
        ]);
        $response = $this->applicationApi->guildCommand($this->guildId, $this->commandId);
        $this->assertEquals(200, $response->status());
        $this->assertEquals($this->guildId, $response->json('guild_id'));
    }

    public function test_update_guild_application_command()
    {
        Http::fake([
            "https://discord.com/api/v10/applications/{$this->applicationId}/guilds/{$this->guildId}/commands/{$this->commandId}" => Http::response([
                'id' => $this->commandId,
                'name' => 'ping',
                'description' => 'Updated ping command',
            ], 200),
        ]);
        $data = ['description' => 'Updated ping command'];
        $response = $this->applicationApi->updateGuildCommand($this->guildId, $this->commandId, $data);
        $this->assertEquals(200, $response->status());
        $this->assertEquals('Updated ping command', $response->json('description'));
    }

    public function test_delete_guild_application_command()
    {
        Http::fake([
            "https://discord.com/api/v10/applications/{$this->applicationId}/guilds/{$this->guildId}/commands/{$this->commandId}" => Http::response(null, 204),
        ]);
        $response = $this->applicationApi->deleteGuildCommand($this->guildId, $this->commandId);
        $this->assertEquals(204, $response->status());
    }

    public function test_bulk_overwrite_guild_application_commands()
    {
        Http::fake([
            "https://discord.com/api/v10/applications/{$this->applicationId}/guilds/{$this->guildId}/commands" => Http::response([
                ['id' => $this->commandId, 'name' => 'ping'],
                ['id' => 'cmd456', 'name' => 'echo'],
            ], 200),
        ]);
        $commands = [
            ['name' => 'ping', 'description' => 'Ping command', 'type' => 1],
            ['name' => 'echo', 'description' => 'Echo command', 'type' => 1],
        ];
        $response = $this->applicationApi->bulkOverwriteGuildCommands($this->guildId, $commands);
        $this->assertEquals(200, $response->status());
        $this->assertCount(2, $response->json());
    }

    public function test_get_guild_command_permissions()
    {
        Http::fake([
            "https://discord.com/api/v10/applications/{$this->applicationId}/guilds/{$this->guildId}/commands/permissions" => Http::response([
                ['id' => $this->commandId, 'permissions' => []],
            ], 200),
        ]);
        $response = $this->applicationApi->guildCommandPermissions($this->guildId);
        $this->assertEquals(200, $response->status());
        $this->assertEquals($this->commandId, $response->json('0.id'));
    }

    public function test_get_guild_command_permission()
    {
        Http::fake([
            "https://discord.com/api/v10/applications/{$this->applicationId}/guilds/{$this->guildId}/commands/{$this->commandId}/permissions" => Http::response([
                'id' => $this->commandId,
                'permissions' => [
                    ['id' => 'role123', 'type' => 1, 'permission' => true],
                ],
            ], 200),
        ]);
        $response = $this->applicationApi->guildCommandPermission($this->guildId, $this->commandId);
        $this->assertEquals(200, $response->status());
        $this->assertEquals($this->commandId, $response->json('id'));
    }

    public function test_edit_guild_command_permissions()
    {
        Http::fake([
            "https://discord.com/api/v10/applications/{$this->applicationId}/guilds/{$this->guildId}/commands/{$this->commandId}/permissions" => Http::response([
                'id' => $this->commandId,
                'permissions' => [
                    ['id' => 'role123', 'type' => 1, 'permission' => true],
                ],
            ], 200),
        ]);
        $data = [
            'permissions' => [
                ['id' => 'role123', 'type' => 1, 'permission' => true],
            ],
        ];
        $response = $this->applicationApi->editGuildCommandPermissions($this->guildId, $this->commandId, $data);
        $this->assertEquals(200, $response->status());
        $this->assertEquals($this->commandId, $response->json('id'));
    }

    public function test_batch_edit_guild_command_permissions()
    {
        Http::fake([
            "https://discord.com/api/v10/applications/{$this->applicationId}/guilds/{$this->guildId}/commands/permissions" => Http::response([
                ['id' => $this->commandId, 'permissions' => []],
            ], 200),
        ]);
        $permissions = [
            [
                'id' => $this->commandId,
                'permissions' => [
                    ['id' => 'role123', 'type' => 1, 'permission' => true],
                ],
            ],
        ];
        $response = $this->applicationApi->batchEditGuildCommandPermissions($this->guildId, $permissions);
        $this->assertEquals(200, $response->status());
        $this->assertEquals($this->commandId, $response->json('0.id'));
    }

    public function test_fluent_interface()
    {
        Http::fake([
            "https://discord.com/api/v10/applications/{$this->applicationId}/commands" => Http::response([], 200),
        ]);
        $applicationApi = $this->discordApi->application($this->applicationId);
        $this->assertEquals($this->applicationId, $applicationApi->getApplicationId());
        $response = $applicationApi->commands();
        $this->assertEquals(200, $response->status());
    }

    public function test_error_on_invalid_application_id()
    {
        Http::fake([
            'https://discord.com/api/v10/applications/*' => Http::response(['message' => '404: Not Found', 'code' => 0], 404),
        ]);
        $this->expectException(RequestException::class);
        try {
            $this->applicationApi->commands();
        } catch (RequestException $e) {
            $this->assertEquals(404, $e->response->status());
            throw $e;
        }
    }
}
