<?php

namespace Tests\Feature;

use App\Models\Player;
use App\Models\PlayerMetadata;
use App\Http\Middleware\ValidateFromGameServer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlayerMetadataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->withoutMiddleware(ValidateFromGameServer::class);
    }

    public function test_bulk_lookup_counts_distinct_players_and_caps_ckeys(): void
    {
        $first = Player::factory()->create(['ckey' => 'aaa']);
        $second = Player::factory()->create(['ckey' => 'bbb']);
        $this->addMetadata($first->id, 'discord_linked');
        $this->addMetadata($first->id, 'discord_linked');
        $this->addMetadata($second->id, 'discord_linked');
        $this->addMetadata($second->id, 'rp_whitelisted');
        $this->addMetadata(null, 'discord_linked');

        $response = $this->postJson(route('api.players.metadata.get-by-data-bulk'), [
            'metadata' => ['discord_linked', 'rp_whitelisted', 'event_runner'],
            'limit' => 1,
        ]);

        $response->assertOk()->assertExactJson([
            'data' => [
                'discord_linked' => ['count' => 2, 'ckeys' => ['aaa']],
                'rp_whitelisted' => ['count' => 1, 'ckeys' => ['bbb']],
                'event_runner' => ['count' => 0, 'ckeys' => []],
            ],
        ]);
    }

    private function addMetadata(?int $playerId, string $metadata): void
    {
        $record = new PlayerMetadata;
        $record->player_id = $playerId;
        $record->metadata = $metadata;
        $record->save();
    }
}
