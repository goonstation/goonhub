<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\Permissions\DiscordSettingPermissions;
use App\Facades\DiscordApi;
use App\Helpers\HasPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\DiscordSettings\UpdateDiscordSettingsRequest;
use App\Models\DiscordSetting;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class DiscordSettingsController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            HasPermission::using(DiscordSettingPermissions::VIEW, only: ['index', 'roles']),
            HasPermission::using(DiscordSettingPermissions::UPDATE, only: ['update']),
        ];
    }

    public function index()
    {
        return Inertia::render('Admin/DiscordSettings/Index', [
            'settings' => DiscordSetting::all(),
        ]);
    }

    public function update(UpdateDiscordSettingsRequest $request)
    {
        $settings = $request->input('settings');

        foreach ($settings as $setting) {
            $setting = DiscordSetting::where('key', $setting['key'])->update([
                'value' => $setting['value'],
            ]);
        }

        return to_route('admin.discord-settings.index');
    }

    public function roles()
    {
        return Cache::remember('discord.roles', now()->addMinutes(5), function () {
            return DiscordApi::guild()->roles()->collect()
                ->sortBy(fn ($role) => strtolower($role['name']))
                ->filter(fn ($role) => $role['name'] !== '@everyone')
                ->paginate(999);
        });
    }
}
