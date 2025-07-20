<?php

namespace App\Console\Commands;

use App\Actions\Fortify\CreateNewUser;
use App\Models\GameAdmin;
use App\Models\GameAdminRank;
use Illuminate\Console\Command;

class InitialSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gh:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bootstrap the application for initial setup';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->call('key:generate');

        $this->call('migrate');

        $this->call('db:seed');

        $this->call('storage:link');

        $this->call('gh:update-geo-lite');

        // User creation

        $this->info('Create your admin user');
        $userName = $this->ask('Name?');
        $userEmail = $this->ask('Email?');
        $userPass = $this->ask('Password?');
        $byondCkey = $this->ask('Byond username?');

        if (! $userName || ! $userEmail || ! $userPass || ! $byondCkey) {
            $this->error('Not enough details.');

            return Command::FAILURE;
        }

        $hostRank = GameAdminRank::where('rank', 'Host')->first();
        $gameAdmin = new GameAdmin;
        $gameAdmin->ckey = ckey($byondCkey);
        $gameAdmin->rank_id = $hostRank->id;
        $gameAdmin->save();

        $action = new CreateNewUser;
        $user = $action->create([
            'name' => $userName,
            'email' => $userEmail,
            'password' => $userPass,
            'password_confirmation' => $userPass,
        ]);
        $user->is_admin = true;
        $user->game_admin_id = $gameAdmin->id;
        $user->email_verified_at = now();
        $user->save();

        $this->info('User created!');

        // API token creation

        $this->info('Create your first API token');
        $tokenName = $this->ask('What name should your API token have?');
        $token = $user->createToken($tokenName);

        $this->newLine();
        $this->info('Your new token is below. Please make a note of this as you won\'t be able to access it again!');
        $this->info($token->plainTextToken);

        return Command::SUCCESS;
    }
}
