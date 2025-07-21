<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Models\LinkedByondUser;
use App\Traits\ManagesUsers;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class LinkByondController extends Controller
{
    use ManagesUsers;

    public function redirect()
    {
        $user = Auth::user();

        if ($user->linkedByond) {
            return redirect()->route('profile.show')
                ->with('error', 'You are already linked to a BYOND account.');
        }

        try {
            return Socialite::driver('bab')->redirect();
        } catch (\Exception $e) {
            return redirect()->route('profile.show')
                ->with('error', 'Failed to redirect to BYOND. Please try again later.');
        }
    }

    public function callback()
    {
        /** @var \SocialiteProviders\BAB\Provider */
        $driver = Socialite::driver('bab');
        $byondUser = $driver->user();
        $user = Auth::user();

        if ($user->linkedByond) {
            return redirect()->route('profile.show')
                ->with('error', 'You are already linked to a BYOND account.');
        }

        $existingByondLink = LinkedByondUser::where('ckey', $byondUser['ckey'])->first();
        if ($existingByondLink) {
            return redirect()->route('profile.show')
                ->with('error', 'This BYOND account is already linked to another user.');
        }

        $this->linkToByond($user, $byondUser['ckey']);

        return redirect()->route('profile.show')
            ->with('success', 'Successfully linked BYOND account.');
    }

    public function unlink()
    {
        $user = Auth::user();

        if (! $user->linkedByond) {
            return redirect()->route('profile.show')
                ->with('error', 'You are not linked to a BYOND account.');
        }

        $user->linkedByond()->delete();

        return redirect()->route('profile.show')
            ->with('success', 'Successfully unlinked BYOND account.');
    }
}
