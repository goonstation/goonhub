<?php

namespace App\Traits;

use App\Http\Requests\GameBuildSettings\StoreRequest;
use App\Http\Requests\GameBuildSettings\UpdateRequest;
use App\Http\Resources\GameBuildSettingResource;
use App\Models\GameBuildSetting;

trait ManagesGameBuildSettings
{
    private function addSetting(StoreRequest $request)
    {
        $setting = new GameBuildSetting;
        $setting->server_id = $request['server_id'];
        $setting->branch = $request['branch'];
        $setting->byond_major = $request['byond_major'];
        $setting->byond_minor = $request['byond_minor'];
        $setting->rustg_version = $request['rustg_version'];
        $setting->rp_mode = isset($request['rp_mode']) ? $request['rp_mode'] : false;
        $setting->map_id = isset($request['map_id']) ? $request['map_id'] : null;
        $setting->save();

        return new GameBuildSettingResource($setting);
    }

    private function updateSetting(UpdateRequest $request, GameBuildSetting $setting)
    {
        foreach ($request->all() as $key => $val) {
            $setting[$key] = $val;
        }

        $setting->save();

        return new GameBuildSettingResource($setting);
    }
}
