<?php

namespace App\Jobs;

use App\Exceptions\ByondOutageException;
use App\Helpers\QueryByond;
use App\Models\Medal;
use App\Models\Player;
use App\Models\PlayerMedal;
use App\Models\PlayerMedalsImported;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use DOMDocument;
use DOMXPath;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportByondMedalsForPlayer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $ckey = '';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $ckey)
    {
        $this->ckey = $ckey;
    }

    private function parseEarnedAt(string $earnedAt)
    {
        $earnedAt = str_replace('Earned ', '', $earnedAt);
        $earnedAt = preg_replace('/\xc2\xa0/', ' ', $earnedAt);

        if (str_starts_with($earnedAt, 'at')) {
            $earnedAt = str_replace('at ', '', $earnedAt);
        } elseif (str_starts_with($earnedAt, 'on')) {
            $earnedAt = str_replace('on ', '', $earnedAt);
            if (preg_match('/^\w+day, \d+:\d+ [ap]m$/i', $earnedAt)) {
                // on <day of the week>, <time>
                // add "last <day>" so carbon knows it's in the past
                $earnedAt = preg_replace('/(\w+day)/i', 'last $1', $earnedAt);
            }
        }

        $ret = Carbon::parse($earnedAt, -7);

        return $ret->setTimezone('UTC')->toIso8601String();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ckey = ckey($this->ckey);
        // echo "Starting medal import for '$ckey'\n";

        $player = Player::where('ckey', $ckey)->first();
        if (! $player) {
            throw new \Exception("Player '$ckey' does not exist");
        }

        $alreadyImported = PlayerMedalsImported::where('player_id', $player->id)->exists();
        if ($alreadyImported) {
            throw new \Exception("Player '$ckey' has already had their Byond medals imported");
        }

        try {
            $res = QueryByond::query("https://www.byond.com/members/$ckey?tab=medals&all=1");
        } catch (ByondOutageException $e) {
            $this->fail($e);

            return;
        } catch (\Throwable) {
            $this->release();

            return;
        }

        $html = $res->getBody();
        $doc = new DOMDocument;
        $internalErrors = libxml_use_internal_errors(true);
        $doc->loadHTML($html);
        libxml_use_internal_errors($internalErrors);
        $xpath = new DOMXPath($doc);

        $medals = [];
        $rows = $xpath->query('//*[@class="medal_table"]/tr');

        if (! $rows->count()) {
            $notice = $xpath->query('//*[@class="notice"]')->item(0);
            if ($notice && str_contains($notice->textContent, 'not active')) {
                // Byond user account is inactive
                // echo "Player is marked inactive on Byond\n";
            }

            return;
        }

        // echo "Iterating over medal elements\n";
        $inOurMedalSection = false;
        foreach ($rows as $row) {
            $title = $xpath->query('.//*[@class="title use_header"]/a', $row)->item(0);
            if ($title) {
                if ($title->textContent === 'Space Station 13 Medals') {
                    $inOurMedalSection = true;
                } else {
                    $inOurMedalSection = false;
                }

                continue;
            }

            if (! $inOurMedalSection) {
                continue;
            }

            $cells = $xpath->query('./td', $row);
            foreach ($cells as $cell) {
                $medalTitle = $xpath->query('.//*[@class="medal_name"]', $cell)->item(0)?->textContent;
                $medalEarned = $xpath->query('.//p[@class="smaller"]', $cell)->item(0)?->textContent;
                if (! $medalTitle || ! $medalEarned) {
                    continue;
                }

                try {
                    $medalEarned = $this->parseEarnedAt($medalEarned);
                } catch (InvalidFormatException $e) {
                    Log::error('Unable to parse earned at date for medal from Byond', [
                        'ckey' => $ckey,
                        'title' => $medalTitle,
                        'earned' => $medalEarned,
                        'error' => $e->getMessage(),
                    ]);

                    continue;
                }

                $medals[] = [
                    'title' => $medalTitle,
                    'earned' => $medalEarned,
                ];
            }
        }

        $report = [
            'updated' => [],
            'inserted' => [],
        ];

        // echo "Inserting medal data into database\n";
        foreach ($medals as $medalData) {
            $medal = Medal::where('title', 'ilike', '%'.$medalData['title'].'%')
                ->first();
            if (! $medal) {
                // echo "Medal '{$medalData['title']}' does not exist\n";
                continue;
            }

            $existingAward = PlayerMedal::where('player_id', $player->id)
                ->where('medal_id', $medal->id)
                ->first();

            if ($existingAward) {
                // echo "Player already has medal '{$medalData['title']}'\n";
                // Check if Byond medal earned at date is earlier than
                // our earned at date. This indicates that a previous import failed
                // and the player has since earned the same medal with the new system.
                // If it is earlier, change the PlayerMedal created_at/updated_at dates
                if (Carbon::parse($medalData['earned'])->isBefore($existingAward->created_at)) {
                    // echo "\tByond medal earned prior to our medal\n";
                    $existingAward->round_id = null;
                    $existingAward->created_at = $medalData['earned'];
                    $existingAward->updated_at = $medalData['earned'];
                    $existingAward->save(['timestamps' => false]);

                    $report['updated'][] = $medalData['title'];
                }

                continue;
            }

            // echo "Inserting award for '{$medalData['title']}'\n";
            $award = new PlayerMedal;
            $award->player_id = $player->id;
            $award->medal_id = $medal->id;
            $award->created_at = $medalData['earned'];
            $award->updated_at = $medalData['earned'];
            $award->save(['timestamps' => false]);

            $report['inserted'][] = $medalData['title'];
        }

        $playerMedalsImported = new PlayerMedalsImported;
        $playerMedalsImported->player_id = $player->id;
        $playerMedalsImported->save();

        // dump($report);
        // echo "Done!\n";
    }
}
