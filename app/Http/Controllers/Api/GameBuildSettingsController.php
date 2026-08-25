<?php

namespace App\Http\Controllers\Api;

use App\Attributes\HasDateRangeFilter;
use App\Attributes\HasServerFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\GameBuildSettings\IndexRequest;
use App\Http\Requests\GameBuildSettings\StoreRequest;
use App\Http\Requests\GameBuildSettings\UpdateRequest;
use App\Http\Resources\GameBuildSettingResource;
use App\Models\GameBuildSetting;
use App\Traits\ManagesGameBuildSettings;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

#[Group('Game Build Settings')]
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
    #[
        HasServerFilter,
        HasDateRangeFilter(name: 'created_at'),
        HasDateRangeFilter(name: 'updated_at'),
    ]
    public function index(IndexRequest $request)
    {
        return GameBuildSettingResource::collection(
            GameBuildSetting::indexFilterPaginate()
        );
    }

    /**
     * Add
     *
     * Add a new game build setting
     */
    public function store(StoreRequest $request)
    {
        return $this->addSetting($request);
    }

    /**
     * Update
     *
     * Update an existing game build setting
     */
    public function update(UpdateRequest $request, GameBuildSetting $setting)
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
