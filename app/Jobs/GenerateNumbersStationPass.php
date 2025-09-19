<?php

namespace App\Jobs;

use App\Facades\GameBridge;
use App\Models\GameServer;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GenerateNumbersStationPass implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct() {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Read and parse source material
        $source = Storage::disk('local')->get('numbers-station-cipher-source.txt');
        if (! $source) {
            return;
        }
        $source = explode("\n", $source);
        $source = array_values(array_filter($source));

        // Generate a random password
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHILMNOPRSTUWY';
        $charactersLength = strlen($characters);
        $password = '';
        $length = 7;
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[mt_rand(0, $charactersLength - 1)];
        }
        $passArray = str_split($password);

        $numbers = '';
        $count = 1;

        // For each password char
        foreach ($passArray as $char) {
            $numBlock = '';

            // Get paragraphs containing char
            $paragraphs = [];
            $icount = 0;
            foreach ($source as $key => $p) {
                if ($icount >= 99) {
                    break;
                }
                if (strpos($p, $char) !== false && strpos($p, $char) <= 100) {
                    $paragraphs[$key] = $p;
                }
                $icount++;
            }
            // Pick a random paragraph
            $randKey = array_rand($paragraphs);
            $paragraph = $source[$randKey];
            $numBlock .= "$randKey ";

            // Get words containing char
            $allWords = explode(' ', $paragraph);
            $ourWords = [];
            $icount = 0;
            foreach ($allWords as $key => $w) {
                if ($icount >= 99) {
                    break;
                }
                if (strpos($w, $char) !== false && strpos($w, $char) <= 100) {
                    $ourWords[$key] = $w;
                }
                $icount++;
            }

            // Pick a random word
            $randKey = array_rand($ourWords);
            $word = $allWords[$randKey];
            $numBlock .= "$randKey ";

            // Get all characters in word matching char
            $allChars = str_split($word);
            $ourChars = [];
            $icount = 0;
            foreach ($allChars as $key => $c) {
                if ($icount >= 99) {
                    break;
                }
                if (strpos($c, $char) !== false && strpos($c, $char) <= 100) {
                    $ourChars[$key] = $c;
                }
                $icount++;
            }

            // Pick a random char
            $randKey = array_rand($ourChars);
            $numBlock .= "$randKey";

            if ($count < strlen($password)) {
                $numbers .= "$numBlock ";
            } else {
                $numbers .= "$numBlock";
            }

            $count++;
        }

        DB::table('numbers_station_password')->updateOrInsert(
            ['id' => 1],
            [
                'password' => $password,
                'numbers' => $numbers,
                'updated_at' => Carbon::now(),
            ]
        );

        if (App::environment('production')) {
            // Send new numbers to all active servers
            GameBridge::servers(GameServer::where('active', true)->get())
                ->force(true)
                ->sendAndForget(['type' => 'numbersStation', 'numbers' => $numbers]);
        }
    }
}
