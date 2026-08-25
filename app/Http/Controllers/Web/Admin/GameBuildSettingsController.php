<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GameBuildSettings\IndexRequest;
use App\Http\Requests\GameBuildSettings\StoreRequest;
use App\Http\Requests\GameBuildSettings\UpdateRequest;
use App\Models\GameBuildSetting;
use App\Traits\ManagesGameBuildSettings;
use Inertia\Inertia;

class GameBuildSettingsController extends Controller
{
    use ManagesGameBuildSettings;

    public function index(IndexRequest $request)
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

    public function store(StoreRequest $request)
    {
        $this->addSetting($request);

        return to_route('admin.builds.settings.index');
    }

    public function edit(GameBuildSetting $setting)
    {
        $setting->load(['gameServer']);

        return Inertia::render('Admin/GameBuilds/Settings/Edit', [
            'setting' => $setting,
        ]);
    }

    public function update(UpdateRequest $request, GameBuildSetting $setting)
    {
        $this->updateSetting($request, $setting);

        return to_route('admin.builds.settings.index');
    }

    public function destroy(GameBuildSetting $setting)
    {
        $setting->delete();

        return ['message' => 'Settings removed'];
    }
}
