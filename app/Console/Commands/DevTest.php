<?php

namespace App\Console\Commands;

use App\Facades\DiscordApi;
use Illuminate\Console\Command;

class DevTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gh:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // $guildId = DiscordSetting::where('key', DiscordSettings::TOMATO_GUILD_ID->value)->first()?->value;
        // $guild = DiscordApi::guild($guildId);
        // $guild->members()

        return Command::SUCCESS;
    }
}
