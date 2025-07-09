<?php

namespace App\Actions\Fortify;

use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use App\Traits\ManagesPlayers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use ManagesPlayers, PasswordValidationRules;

    /**
     * Create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $suffix = Player::AUTH_SUFFIXES['goonhub'];
        $input['ckey'] = ckey($input['name'].$suffix);
        $input['email'] = strtolower($input['email']);

        Validator::make($input, [
            'name' => ['required', 'regex:/^[a-zA-Z0-9\s]+$/', 'max:255'],
            'ckey' => ['required', 'unique:players,ckey'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ], [
            'name.required' => 'Please enter a username.',
            'name.regex' => 'The username can only contain letters, numbers, and spaces.',
            'name.max' => 'Please enter a username less than 255 characters.',
            'ckey.unique' => 'That username has already been taken.',
            'email.unique' => 'That email has already been taken.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'Please enter an email less than 255 characters.',
        ])->validate();

        return DB::transaction(function () use ($input) {
            $player = $this->createPlayer($input['name'], 'goonhub');

            return tap(User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'player_id' => $player->id,
            ]), function (User $user) {
                // $this->createTeam($user);
            });
        });
    }

    /**
     * Create a personal team for the user.
     */
    protected function createTeam(User $user): void
    {
        $user->ownedTeams()->save(Team::forceCreate([
            'user_id' => $user->id,
            'name' => explode(' ', $user->name, 2)[0]."'s Team",
            'personal_team' => true,
        ]));
    }
}
