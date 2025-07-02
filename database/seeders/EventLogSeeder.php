<?php

namespace Database\Seeders;

use App\Models\Events\EventLog;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EventLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lorem = ['Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'Nullam maximus in sem a lobortis. Proin convallis accumsan diam, eget luctus nibh ullamcorper eu.',
            'Aliquam mi nulla, scelerisque id tincidunt a, commodo quis purus.',
            'Nullam vestibulum tincidunt massa ut porta. Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'Donec a est sit amet nunc aliquam blandit nec sed turpis.',
            'Suspendisse tristique tempor turpis, id convallis tortor interdum vitae.',
            'Donec at felis a ipsum porttitor placerat eget in metus.',
            'Donec laoreet eget leo sed imperdiet. Praesent id dui felis.',
            'Sed scelerisque, nulla quis luctus laoreet, erat sem efficitur elit, ut feugiat libero magna fringilla est.',
        ];
        $log_types = [
            'access',
            'admin',			// ! Admin actions
            'ahelp',			// ! Ahelps and admin responses
            'audit',			// ! Admin auditing stuff
            'bombing',		// ! Explosions
            'combat',			// ! People fighting or smashing shit
            'debug',			// ! Debug information
            'diary',			// ! Diary
            'game',				// ! Diary only
            'mhelp',			// ! Used for diary too
            'ooc',				// ! OOC
            'pdamsg',			// ! PDA messaging
            'say',				// ! IC Speech
            'speech',			// ! Ingame logs only, say + whisper
            'signalers',	// ! Remote signallers
            'station',		// ! Interactions with/between inanimate objects, as well as the station as a whole
            'telepathy',	// ! Telepathy gene messages
            'vehicle',		// ! Vehicle stuff
            'whisper',		// ! Whisper messages
            'topic',			// ! Topic() logs
            'gamemode',		// ! Core gamemode stuff like game mode selection, blob starts, flock planting, etc
            'chemistry', 	// ! Non-combat chemistry interactions
            'tgui', 			// ! TGUI interactions
        ];
        $log_sources = ['TheWorstPlayer', 'Badmin', 'Mr. Muggles', 'The clown', 'DatabaseSeeder123'];

        for ($i = 0; $i < 300; $i++) {
            EventLog::insertOrIgnore([
                'id' => $i,
                'round_id' => 1,
                'type' => $log_types[array_rand($log_types)],
                'source' => $log_sources[array_rand($log_sources)],
                'message' => $lorem[array_rand($lorem)],
                'created_at' => Carbon::now()->addSeconds($i),
                'updated_at' => Carbon::now()->addSeconds($i),
            ]);
        }
    }
}
