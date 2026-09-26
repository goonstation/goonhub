<?php

namespace Tests\Feature;

use App\Http\Middleware\ValidateFromGameServer;
use App\Models\Player;
use App\Models\PlayerMetadata;
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

    public function test_bulk_store_adds_only_missing_metadata(): void
    {
        $player = Player::factory()->create();
        $other = Player::factory()->create();
        $this->addMetadata($player->id, 'discord_linked');
        $this->addMetadata($other->id, 'rp_whitelisted');

        $response = $this->postJson(route('api.players.metadata.store-bulk'), [
            'player_id' => $player->id,
            'metadata' => ['discord_linked', 'rp_whitelisted', 'event_runner'],
        ]);

        $response->assertOk();
        $this->assertEqualsCanonicalizing(
            ['discord_linked', 'rp_whitelisted', 'event_runner'],
            PlayerMetadata::where('player_id', $player->id)->pluck('metadata')->all()
        );
        $this->assertSame(1, PlayerMetadata::where('player_id', $other->id)->count());
    }

    private function addMetadata(?int $playerId, string $metadata): void
    {
        $record = new PlayerMetadata;
        $record->player_id = $playerId;
        $record->metadata = $metadata;
        $record->save();
    }
}
