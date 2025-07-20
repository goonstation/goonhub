<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameBuildSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GameBuildSettingsController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/GameBuilds/Settings/Index', [
            'settings' => Inertia::lazy(function () {
                return GameBuildSetting::with(['gameServer', 'map'])
                    ->withAggregate('gameServer', 'id')
                    ->indexFilterPaginate();
            }),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/GameBuilds/Settings/Create', [
            'existingServers' => GameBuildSetting::select('server_id')->get()->pluck('server_id'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'server_id' => 'required|string|exists:game_servers,server_id|unique:game_build_settings,server_id',
            'map_id' => 'nullable|string|exists:maps,map_id',
            'branch' => 'nullable|string',
            'byond_major' => 'required|integer',
            'byond_minor' => 'required|integer',
            'rustg_version' => 'required|string',
            'rp_mode' => 'required|boolean',
        ]);

        $setting = new GameBuildSetting;
        $setting->server_id = $data['server_id'];
        $setting->map_id = $data['map_id'];
        $setting->branch = $data['branch'] ?: 'master';
        $setting->byond_major = $data['byond_major'];
        $setting->byond_minor = $data['byond_minor'];
        $setting->rustg_version = $data['rustg_version'];
        $setting->rp_mode = $data['rp_mode'];
        $setting->save();

        return to_route('admin.builds.settings.index');
    }

    public function edit(GameBuildSetting $setting)
    {
        $setting->load(['gameServer']);

        return Inertia::render('Admin/GameBuilds/Settings/Edit', [
            'setting' => $setting,
        ]);
    }

    public function update(Request $request, GameBuildSetting $setting)
    {
        $data = $request->validate([
            'map_id' => 'nullable|string|exists:maps,map_id',
            'branch' => 'nullable|string',
            'byond_major' => 'required|integer',
            'byond_minor' => 'required|integer',
            'rustg_version' => 'required|string',
            'rp_mode' => 'required|boolean',
        ]);

        $setting->map_id = $data['map_id'];
        $setting->branch = $data['branch'] ?: 'master';
        $setting->byond_major = $data['byond_major'];
        $setting->byond_minor = $data['byond_minor'];
        $setting->rustg_version = $data['rustg_version'];
        $setting->rp_mode = $data['rp_mode'];
        $setting->save();

        return to_route('admin.builds.settings.index');
    }

    public function destroy(GameBuildSetting $setting)
    {
        $setting->delete();

        return ['message' => 'Settings removed'];
    }
}
