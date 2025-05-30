<?php

namespace App\Jobs;

use App\Models\Changelog;
use GrahamCampbell\GitHub\Facades\GitHub;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BuildChangelog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $entries = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    private function flushEntry($date, $entry)
    {
        if (! count($entry)) {
            return false;
        }
        $this->entries[$date][] = $entry;

        return true;
    }

    private function parseDate($dumbDate)
    {
        $dateParts = explode(' ', $dumbDate);

        if (count($dateParts) < 4) {
            return 'Invalid Date';
        }

        // $dayOfWeek = $dateParts[0]; // yeah let's just...let the view layer handle that
        $month = $dateParts[1];
        $day = $dateParts[2];
        $year = $dateParts[3];

        return date('Y-m-d H:i:s', strtotime("$month $day $year"));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            /** @var \Github\Client */
            $conn = GitHub::connection();
            $changelog = $conn->repo()->contents()->download(
                config('goonhub.github_organization'),
                config('goonhub.github_repo'),
                'strings/changelog.txt'
            );
        } catch (\Throwable) {
            return;
        }

        if (! $changelog) {
            return;
        }
        $lines = preg_split('/\r\n|\r|\n/', $changelog);

        $entry = [];
        $date = null;
        foreach ($lines as $line) {
            if (! $line) {
                continue;
            }
            if (str_starts_with($line, '#')) {
                continue;
            }

            if (str_starts_with($line, '(t)')) {
                if ($this->flushEntry($date, $entry)) {
                    $entry = [];
                }
                $date = $this->parseDate(trim(explode('(t)', $line)[1]));
            } elseif (str_starts_with($line, '(u)')) {
                if ($this->flushEntry($date, $entry)) {
                    $entry = [];
                }
                $entry['user'] = trim(explode('(u)', $line)[1]);
            } elseif (str_starts_with($line, '(p)')) {
                $entry['pr'] = trim(explode('(p)', $line)[1]);
            } elseif (str_starts_with($line, '(e)')) {
                $emojiLabels = trim(explode('(e)', $line)[1]);
                $parts = explode('|', $emojiLabels);
                $emojis = $parts[0];
                $labels = null;
                if (count($parts) > 1) {
                    $labels = $parts[1];
                }
                $entry['emojiLabels'] = [
                    'emojis' => $emojis,
                    'labels' => $labels,
                ];
            } elseif (str_starts_with($line, '(*)')) {
                $entry['major'][] = trim(explode('(*)', $line)[1]);
            } elseif (str_starts_with($line, '(+)')) {
                $entry['minor'][] = trim(explode('(+)', $line)[1]);
            }
        }
        $this->flushEntry($date, $entry);

        $entries = json_encode($this->entries);
        $item = Changelog::first();
        if ($item) {
            $item->update(['entries' => $entries]);
        } else {
            $item = new Changelog;
            $item->entries = $entries;
            $item->save();
        }
    }
}
