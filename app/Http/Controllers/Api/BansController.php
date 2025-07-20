<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BanRequest;
use App\Http\Requests\IndexQueryRequest;
use App\Http\Resources\BanDetailResource;
use App\Http\Resources\BanResource;
use App\Http\Resources\Bans\CheckBanResource;
use App\Models\Ban;
use App\Models\BanDetail;
use App\Models\GameAdmin;
use App\Models\Player;
use App\Models\PlayerNote;
use App\Rules\DateRange;
use App\Rules\Range;
use App\Traits\ManagesBans;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BansController extends Controller
{
    use ManagesBans;

    /**
     * List
     *
     * List filtered and paginated bans
     *
     * @return AnonymousResourceCollection<LengthAwarePaginator<BanResource>>
     */
    public function index(IndexQueryRequest $request)
    {
        $request->validate([
            'filters.id' => 'int',
            /** @example main1 */
            'filters.server' => 'string',
            'filters.admin_ckey' => 'string',
            'filters.reason' => 'string',
            'filters.original_ban_ckey' => 'string',
            'filters.ckey' => 'string',
            'filters.comp_id' => 'string',
            'filters.ip' => 'string',
            'filters.requires_appeal' => 'boolean',
            /**
             * A value, comparison, or range
             *
             * @example 1 or >= 1 or 1-10
             */
            'filters.details' => new Range,
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
            /**
             * A date or date range
             *
             * @example 2023/01/30 12:00:00 - 2023/02/01 12:00:00
             */
            'filters.expires_at' => new DateRange,
            /**
             * A date or date range
             *
             * @example 2023/01/30 12:00:00 - 2023/02/01 12:00:00
             */
            'filters.deleted_at' => new DateRange,
        ]);

        return BanResource::collection(
            Ban::withTrashed()
                ->with(['gameAdmin', 'gameRound', 'details', 'originalBanDetail'])
                ->indexFilterPaginate()
        );
    }

    /**
     * Check
     *
     * Check if a ban exists for given player data
     */
    public function check(Request $request)
    {
        $request->validate([
            'ckey' => 'required_without_all:comp_id,ip,player_id|string|nullable',
            'comp_id' => 'required_without_all:ckey,ip,player_id|string|nullable',
            'ip' => 'required_without_all:ckey,comp_id,player_id|ip|nullable',
            'player_id' => 'required_without_all:ckey,comp_id,ip|integer|nullable',
            'server_id' => 'nullable|string',
        ]);

        $ckey = $request->input('ckey', '');
        $compId = $request->input('comp_id', '');
        $ip = $request->input('ip', '');
        $serverId = $request->input('server_id');
        $playerId = $request->input('player_id');

        /*
        * Criteria:
        * Get any existing regular ban that matches any of ckey, compId, ip
        * And apply to all servers, or have the serverId we provide
        * And are permanent, or have yet to expire
        */

        $detailsExist = BanDetail::select(DB::raw(1))
            ->whereColumn('bans.id', 'ban_details.ban_id')
            ->where(function ($q) use ($ckey, $compId, $ip, $playerId) {
                // Check any of the ban details match the provided player details
                if ($ckey) {
                    $q->orWhere('ckey', $ckey);
                }
                if ($compId) {
                    $q->orWhere('comp_id', $compId);
                }
                if ($ip) {
                    $q->orWhere('ip', $ip);
                }
                if ($playerId) {
                    $q->orWhere('player_id', $playerId);
                }
            });

        $ban = Ban::select(['*'])
            ->with([
                'gameAdmin:id,ckey,name',
                'details:id,ban_id,ckey,comp_id,ip,player_id,created_at',
            ])
            ->where(function ($query) use ($serverId) {
                // Check if the ban applies to all servers, or the server id we were provided
                $query->whereNull('server_id');
                if ($serverId) {
                    $query->orWhere('server_id', $serverId);
                }
            })
            ->where(function ($query) {
                // Check the ban is permanent, or has yet to expire
                $query->whereNull('expires_at')->orWhere('expires_at', '>', Carbon::now()->toDateTimeString());
            })
            ->whereExists($detailsExist)
            ->orderBy('id', 'desc')
            ->firstOrFail();

        return new CheckBanResource($ban);
    }

    /**
     * Add
     *
     * Add a ban for given player data
     */
    public function store(BanRequest $request)
    {
        return $this->addBan($request);
    }

    /**
     * Update
     *
     * Update an existing ban
     */
    public function update(BanRequest $request, Ban $ban)
    {
        try {
            return $this->updateBan($request, $ban);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Delete
     *
     * Delete an existing ban
     */
    public function destroy(Request $request, Ban $ban)
    {
        $data = $this->validate($request, [
            'game_admin_ckey' => 'required|exists:game_admins,ckey',
        ]);

        $gameAdmin = GameAdmin::where('ckey', $data['game_admin_ckey'])->first();

        $ban->deleted_by = $gameAdmin->id;
        $ban->save();
        $ban->delete();

        return ['message' => 'Ban removed'];
    }

    /**
     * Add ban details
     *
     * Add new player details to an existing ban. This should be used when an evasion attempt is detected.
     */
    public function addDetails(Request $request, Ban $ban)
    {
        $data = $this->validate($request, [
            'game_admin_ckey' => 'nullable|exists:game_admins,ckey',
            'round_id' => 'nullable|integer|exists:game_rounds,id',
            'ckey' => 'required_without_all:comp_id,ip,player_id|nullable',
            'comp_id' => 'required_without_all:ckey,ip,player_id|nullable',
            'ip' => 'required_without_all:ckey,comp_id,player_id|ip|nullable',
            'player_id' => 'required_without_all:ckey,comp_id,ip|integer|nullable',
            'evasion' => 'nullable|boolean',
        ]);

        $banDetail = new BanDetail;
        $banDetail->ckey = isset($data['ckey']) ? $data['ckey'] : null;
        $banDetail->comp_id = isset($data['comp_id']) ? $data['comp_id'] : null;
        $banDetail->ip = isset($data['ip']) ? $data['ip'] : null;
        $banDetail->player_id = isset($data['player_id']) ? $data['player_id'] : null;
        $ban->details()->save($banDetail);

        $banDetail->setAttribute(
            'originalBanDetail',
            BanDetail::withTrashed()
                ->where('ban_id', $banDetail->ban_id)
                ->oldest()
                ->first()
        );

        if (isset($data['evasion']) && $data['evasion']) {
            $gameAdmin = GameAdmin::where('ckey', ckey($data['game_admin_ckey']))->first();
            $player = null;
            $ckey = isset($data['ckey']) ? ckey($data['ckey']) : null;
            if (isset($data['player_id']) && $data['player_id']) {
                $player = Player::find($data['player_id']);
            } elseif ($ckey) {
                $player = Player::where('ckey', $ckey)->first();
            }

            $note = new PlayerNote;
            if ($player) {
                $note->player_id = $player->id;
            } else {
                $note->ckey = $ckey;
            }
            $note->game_admin_id = $gameAdmin->id;
            $note->server_id = $ban->server_id;
            $note->round_id = isset($data['round_id']) ? $data['round_id'] : null;
            $note->note = sprintf(
                'Ban evasion attempt detected, added connection details (IP: %s, CompID: %s) to ban. Original ban ckey: %s. Reason: %s',
                $banDetail->ip,
                $banDetail->comp_id,
                /** @phpstan-ignore-next-line */
                $banDetail->originalBanDetail->ckey,
                $ban->reason
            );
            $note->save();
        }

        return new BanDetailResource($banDetail);
    }

    /**
     * Remove ban details
     *
     * Remove ban details associated with a ban
     */
    public function destroyDetail(BanDetail $banDetail)
    {
        $banDetail->delete();

        return ['message' => 'Ban detail removed'];
    }
}
