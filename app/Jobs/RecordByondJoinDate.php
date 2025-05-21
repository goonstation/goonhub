<?php

namespace App\Jobs;

use App\Helpers\QueryByond;
use App\Models\Player;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecordByondJoinDate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $playerId;

    private $ckey;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $playerId, string $ckey)
    {
        $this->playerId = $playerId;
        $this->ckey = $ckey;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $byondJoinDate = $this->getByondJoinDate($this->ckey);
        Player::where('id', $this->playerId)
            ->update([
                'byond_join_date' => $byondJoinDate,
            ]);
    }

    /**
     * Query byond for the join date of an account
     *
     * @return string|null
     */
    private function getByondJoinDate(string $ckey)
    {
        $response = null;
        try {
            $response = QueryByond::query("https://secure.byond.com/members/$ckey?format=text");
        } catch (\Throwable) {
            return null;
        }

        if ($response->failed()) {
            return null;
        }
        preg_match('/joined = "(.*)"/i', $response->body(), $matches);
        if (empty($matches[1])) {
            return null;
        }

        return $matches[1];
    }
}
