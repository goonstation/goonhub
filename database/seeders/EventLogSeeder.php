<?php

namespace Database\Seeders;

use App\Models\GameRound;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventLogSeeder extends Seeder
{
    private const LOGS_PER_ROUND = 15000;

    private const ROUNDS_TO_SEED = 10;

    private const BATCH_SIZE = 100;

    /**
     * Pre-computed log entries (type => [source, message] pairs).
     * No Faker calls - pure static data for maximum speed.
     */
    private const LOG_TEMPLATES = [
        ['say', 'Elara Voss', 'elaravoss', 'says, "Hey, anyone around?"'],
        ['say', 'Marcus Chen', 'marcuschen', 'says, "I need help over here!"'],
        ['say', 'Zara Nightingale', 'zaranightingale', 'says, "Where is the captain?"'],
        ['say', 'Kai Tanaka', 'kaitanaka', 'says, "Security to my location please!"'],
        ['say', 'Luna Sterling', 'lunasterling', 'says, "Medical emergency!"'],
        ['combat', 'Rex Gunner', 'rexgunner', 'attacked someone with toolbox'],
        ['combat', 'Nova Blackwood', 'novablackwood', 'hit someone with crowbar'],
        ['combat', 'Ash Wolfe', 'ashwolfe', 'stunned someone with stun baton'],
        ['combat', 'River Stone', 'riverstone', 'pushed someone aggressively'],
        ['chemistry', 'Phoenix Blake', 'phoenixblake', 'mops The floor with chemicals (<b>Contents:</b> <i>blood (19.5)</i>)'],
        ['chemistry', 'Jade Morgan', 'jademorgan', 'dispenses water (25u) from the chem dispenser'],
        ['admin', 'Storm Richards', 'stormrichards', 'has checked the player panel'],
        ['admin', 'Blake Harper', 'blakeharper', 'teleported to coordinates'],
        ['ooc', 'Quinn Foster', 'quinnfoster', 'OOC: lol nice one'],
        ['ooc', 'Sky Walker', 'skywalker', 'OOC: gg everyone'],
        ['whisper', 'Echo Valentine', 'echovalentine', 'whispers, "I think he is the traitor..."'],
        ['whisper', 'Raven Cross', 'ravencross', 'whispers, "Meet me in maintenance."'],
        ['ahelp', 'Atlas Grey', 'atlasgrey', 'ADMINHELP: Someone is griefing in science!'],
        ['mhelp', 'Indigo James', 'indigojames', 'MENTORHELP: How do I use this machine?'],
        ['bombing', 'Onyx Smith', 'onyxsmith', 'Explosion at (150,150,1) with power 45.5'],
        ['pdamsg', 'Elara Voss', 'elaravoss', 'PDA message to Someone: "Hey, meet me at the bar."'],
        ['debug', 'System', 'system', 'Runtime error in /obj/machinery/computer'],
        ['diary', 'Marcus Chen', 'marcuschen', 'wrote in their diary: "Today was interesting."'],
        ['tgui', 'Zara Nightingale', 'zaranightingale', 'interacted with TGUI interface: chem_dispenser'],
    ];

    public function run(): void
    {
        DB::disableQueryLog();

        $rounds = GameRound::query()
            ->whereNotNull('ended_at')
            ->orderBy('id', 'desc')
            ->limit(self::ROUNDS_TO_SEED)
            ->get(['id', 'created_at', 'ended_at']);

        if ($rounds->isEmpty()) {
            $this->command->error('No game rounds found. Run GameRoundSeeder first.');

            return;
        }

        // Pre-build source strings once
        $templates = $this->buildTemplates();
        $templateCount = count($templates);

        $this->command->info('Seeding '.number_format(self::LOGS_PER_ROUND * $rounds->count()).' event logs...');

        foreach ($rounds as $roundIndex => $round) {
            $startTime = microtime(true);

            $roundStart = strtotime($round->created_at);
            $roundEnd = strtotime($round->ended_at);
            $duration = max(1, $roundEnd - $roundStart);

            $logs = [];

            for ($i = 0; $i < self::LOGS_PER_ROUND; $i++) {
                // Simple linear timestamp distribution
                $timestamp = $roundStart + (int) (($i / self::LOGS_PER_ROUND) * $duration);
                $template = $templates[$i % $templateCount];

                $logs[] = [
                    'round_id' => $round->id,
                    'type' => $template['type'],
                    'source' => $template['source'],
                    'message' => $template['message'].' at ('.mt_rand(50, 250).','.mt_rand(50, 250).','.mt_rand(1, 3).')',
                    'created_at' => date('Y-m-d H:i:s', $timestamp).'.'.str_pad((string) mt_rand(0, 999), 3, '0', STR_PAD_LEFT),
                    'updated_at' => null,
                ];

                if (count($logs) >= self::BATCH_SIZE) {
                    DB::table('events_logs')->insert($logs);
                    $logs = [];
                }
            }

            if (! empty($logs)) {
                DB::table('events_logs')->insert($logs);
            }

            $elapsed = round(microtime(true) - $startTime, 1);
            $this->command->info('  Round '.($roundIndex + 1)."/{$rounds->count()} (ID: {$round->id}) - {$elapsed}s");
        }

        $this->command->info('Done!');
    }

    /**
     * @return array<int, array{type: string, source: string, message: string}>
     */
    private function buildTemplates(): array
    {
        $templates = [];

        foreach (self::LOG_TEMPLATES as $t) {
            $templates[] = [
                'type' => $t[0],
                'source' => "<span class='name'>{$t[1]}</span> (<a href='byond://?src=%admin_ref%;action=adminplayeropts;targetckey={$t[2]}' title='Player Options'>{$t[1]}</a>)",
                'message' => $t[3],
            ];
        }

        return $templates;
    }
}
