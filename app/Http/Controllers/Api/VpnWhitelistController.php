<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexQueryRequest;
use App\Http\Requests\VpnWhitelist\StoreRequest;
use App\Http\Resources\VpnWhitelistResource;
use App\Models\VpnWhitelist;
use App\Rules\DateRange;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @tags VPN Whitelist
 */
class VpnWhitelistController extends Controller
{
    /**
     * List
     *
     * List paginated and filtered VPN whitelist rules
     *
     * @return AnonymousResourceCollection<LengthAwarePaginator<VpnWhitelistResource>>
     */
    public function index(IndexQueryRequest $request)
    {
        $request->validate([
            'filters.id' => 'int',
            'filters.ckey' => 'string',
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

        return VpnWhitelistResource::collection(
            VpnWhitelist::with(['gameAdmin.player'])
                ->indexFilterPaginate()
        );
    }

    /**
     * Search
     *
     * Search for a ckey in the whitelist
     */
    public function search(Request $request)
    {
        $data = $request->validate([
            'ckey' => 'required|alpha_num',
        ]);

        $whitelisted = VpnWhitelist::where('ckey', $data['ckey'])->exists();

        return ['data' => [
            /** @var bool */
            'whitelisted' => $whitelisted,
        ]];
    }

    /**
     * Add
     *
     * Add a player into the whitelist. This will allow them to skip VPN checks.
     */
    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        $gameAdmin = $request->getGameAdmin();

        $entry = VpnWhitelist::firstOrCreate(
            ['ckey' => $data['ckey']],
            [
                'game_admin_id' => $gameAdmin ? $gameAdmin->id : null,
            ]
        );

        return new VpnWhitelistResource($entry);
    }

    /**
     * Delete
     *
     * Delete a whitelist entry by ckey
     */
    public function destroy(Request $request)
    {
        $data = $request->validate([
            'ckey' => 'required|alpha_num',
        ]);

        VpnWhitelist::where('ckey', $data['ckey'])->delete();

        return ['message' => 'VPN check whitelist entry removed'];
    }
}
