<?php

namespace App\Jobs;

use App\Facades\DiscordApi;
use App\Models\DiscordSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GrantDiscordRole implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public string $discordId,
    ) {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $grantDiscordRole = DiscordSetting::where('key', DiscordSetting::GRANT_ROLE_WHEN_LINKED)
            ->whereNotNull('value')
            ->first();

        if ($grantDiscordRole) {
            DiscordApi::guild()->addMemberRole(
                $this->discordId,
                $grantDiscordRole->value,
                'Linked Goonhub account'
            );
        }
    }
}
