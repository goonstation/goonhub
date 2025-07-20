<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GameBuildSettingCreateRequest;
use App\Http\Requests\GameBuildSettingUpdateRequest;
use App\Http\Requests\IndexQueryRequest;
use App\Http\Resources\GameBuildSettingResource;
use App\Models\GameBuildSetting;
use App\Rules\DateRange;
use App\Traits\ManagesGameBuildSettings;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @tags Game Build Settings
 */
class GameBuildSettingsController extends Controller
{
    use ManagesGameBuildSettings;

    /**
     * List
     *
     * List paginated and filtered game build settings
     *
     * @return AnonymousResourceCollection<LengthAwarePaginator<GameBuildSettingResource>>
     */
    public function index(IndexQueryRequest $request)
    {
        $request->validate([
            'filters.id' => 'int',
            'filters.server' => 'string',
            'filters.branch' => 'string',
            'filters.byond_major' => 'int',
            'filters.byond_minor' => 'int',
            'filters.rustg_version' => 'string',
            'filters.rp_mode' => 'boolean',
            'filters.map_id' => 'string',
            /**
             * A date or date range
             *
             * @example 2023/01/30 12:00:00 - 2023/02/01 12:00:00
             */
            'filters.created_at' => new DateRange,
            /**
             * A date or date range
             *
             * @example 2023/01/30 12:00:00 - 2023/02/01 12:00:00
             */
            'filters.updated_at' => new DateRange,
        ]);

        return GameBuildSettingResource::collection(
            GameBuildSetting::indexFilterPaginate()
        );
    }

    /**
     * Add
     *
     * Add a new game build setting
     */
    public function store(GameBuildSettingCreateRequest $request)
    {
        return $this->addSetting($request);
    }

    /**
     * Update
     *
     * Update an existing game build setting
     */
    public function update(GameBuildSettingUpdateRequest $request, GameBuildSetting $setting)
    {
        return $this->updateSetting($request, $setting);
    }

    /**
     * Delete
     *
     * Delete an existing game build setting
     */
    public function destroy(GameBuildSetting $setting)
    {
        $setting->delete();

        return ['message' => 'Setting removed'];
    }
}
