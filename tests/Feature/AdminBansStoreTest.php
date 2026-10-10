<?php

namespace Tests\Feature;

use App\Models\Ban;
use App\Models\GameServer;
use App\Models\Player;
use App\Models\PlayerAdmin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AdminBansStoreTest extends TestCase
{
    use RefreshDatabase;

    private PlayerAdmin $gameAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();

        $player = Player::factory()->create();
        $this->gameAdmin = PlayerAdmin::forceCreate(['player_id' => $player->id]);
        $this->actingAs(User::factory()->create(['is_admin' => true, 'player_id' => $player->id]));

        foreach (['main1', 'main3', 'main4'] as $serverId) {
            GameServer::forceCreate([
                'server_id' => $serverId,
                'name' => $serverId,
                'short_name' => $serverId,
                'address' => 'localhost',
                'port' => 1000,
            ]);
        }
    }

    private function banData(array $overrides = []): array
    {
        return [
            'game_admin_id' => $this->gameAdmin->id,
            'ckey' => 'bannedperson',
            'reason' => 'Grief',
            'duration' => 3600,
            ...$overrides,
        ];
    }

    public function test_selecting_multiple_servers_creates_one_ban_per_server(): void
    {
        $this->post(route('admin.bans.store'), $this->banData(['server_ids' => ['main3', 'main4']]))
            ->assertRedirect(route('admin.bans.index'));

        $bans = Ban::with('originalBanDetail')->orderBy('server_id')->get();
        $this->assertSame(['main3', 'main4'], $bans->pluck('server_id')->all());
        $this->assertSame(['bannedperson', 'bannedperson'], $bans->pluck('originalBanDetail.ckey')->all());
        $this->assertTrue($bans->every(fn (Ban $ban) => $ban->server_group === null && $ban->expires_at !== null));
    }

    public function test_selecting_no_servers_creates_a_single_ban_for_all_servers(): void
    {
        $this->post(route('admin.bans.store'), $this->banData(['server_ids' => []]))
            ->assertRedirect(route('admin.bans.index'));

        $this->assertSame([null], Ban::pluck('server_id')->all());
    }

    public function test_an_unknown_server_rejects_the_whole_request(): void
    {
        $this->post(route('admin.bans.store'), $this->banData(['server_ids' => ['main3', 'nope']]))
            ->assertSessionHasErrors('server_ids');

        $this->assertSame(0, Ban::count());
    }
}
