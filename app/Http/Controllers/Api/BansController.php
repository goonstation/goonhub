<?php

namespace App\Http\Controllers\Api;

use App\Attributes\HasGameAdminCkeyBody;
use App\Attributes\HasGameAdminCkeyQuery;
use App\Attributes\HasGameAdminIdBody;
use App\Attributes\HasGameAdminIdQuery;
use App\Attributes\HasServerIdBody;
use App\Http\Controllers\Controller;
use App\Http\Requests\Bans\AddDetailsRequest;
use App\Http\Requests\Bans\CheckRequest;
use App\Http\Requests\Bans\DestroyRequest;
use App\Http\Requests\Bans\StoreRequest;
use App\Http\Requests\IndexQueryRequest;
use App\Http\Resources\BanDetailResource;
use App\Http\Resources\BanResource;
use App\Models\Ban;
use App\Models\BanDetail;
use App\Models\Player;
use App\Models\PlayerNote;
use App\Rules\DateRange;
use App\Rules\Range;
use App\Services\CommonRequest;
use App\Traits\ManagesBans;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class BansController extends Controller implements HasMiddleware
{
    use ManagesBans;

    public function __construct(
        private readonly CommonRequest $commonRequest,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('ability:view bans', only: ['index', 'check']),
            new Middleware('ability:add bans', only: ['store', 'addDetails']),
            new Middleware('ability:update bans', only: ['update']),
            new Middleware('ability:delete bans', only: ['destroy', 'destroyDetail']),
        ];
    }

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
            Ban::forApi()
                ->withTrashed()
                ->with(['gameAdmin', 'gameRound', 'details', 'originalBanDetail'])
                ->indexFilterPaginate()
        );
    }

    /**
     * Check
     *
     * Check if a ban exists for given player data
     */
    public function check(CheckRequest $request)
    {
        /*
        * Criteria:
        * Get any existing regular ban that matches any of ckey, compId, ip
        * And apply to all servers, or have the serverId we provide
        * And are permanent, or have yet to expire
        */

        $detailsExist = BanDetail::select(DB::raw(1))
            ->whereColumn('bans.id', 'ban_details.ban_id')
            ->where(function ($q) use ($request) {
                // Check any of the ban details match the provided player details
                if ($request->filled('ckey')) {
                    $q->orWhere('ckey', $request->validated('ckey'));
                }
                if ($request->filled('comp_id')) {
                    $q->orWhere('comp_id', $request->validated('comp_id'));
                }
                if ($request->filled('ip')) {
                    $q->orWhere('ip', $request->validated('ip'));
                }
                if ($request->filled('player_id')) {
                    $q->orWhere('player_id', $request->validated('player_id'));
                }
            });

        $ban = Ban::forApi()
            ->with(['gameAdmin.player', 'details'])
            ->where(function ($query) {
                // Check the ban is permanent, or has yet to expire
                $query->whereNull('expires_at')->orWhere('expires_at', '>', Carbon::now()->toDateTimeString());
            })
            ->whereExists($detailsExist)
            ->orderBy('id', 'desc')
            ->firstOrFail();

        return new BanResource($ban);
    }

    /**
     * Add
     *
     * Add a ban for given player data
     */
    #[HasServerIdBody, HasGameAdminIdBody, HasGameAdminCkeyBody]
    public function store(StoreRequest $request)
    {
        return $this->addBan($request);
    }

    /**
     * Update
     *
     * Update an existing ban
     */
    #[HasServerIdBody, HasGameAdminIdBody, HasGameAdminCkeyBody]
    public function update(StoreRequest $request, Ban $ban)
    {
        try {
            return $this->updateBan($request, $ban);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Delete
     *
     * Delete an existing ban
     */
    #[HasGameAdminIdQuery, HasGameAdminCkeyQuery]
    public function destroy(DestroyRequest $request, Ban $ban)
    {
        $gameAdmin = $this->commonRequest->targetGameAdmin();

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
    #[HasGameAdminIdBody, HasGameAdminCkeyBody]
    public function addDetails(AddDetailsRequest $request, Ban $ban)
    {
        $data = collect($request->validated());

        $banDetail = new BanDetail;
        $banDetail->ckey = $data->has('ckey') ? $data['ckey'] : null;
        $banDetail->comp_id = $data->has('comp_id') ? $data['comp_id'] : null;
        $banDetail->ip = $data->has('ip') ? $data['ip'] : null;
        $banDetail->player_id = $data->has('player_id') ? $data['player_id'] : null;
        $ban->details()->save($banDetail);

        $banDetail->setAttribute(
            'originalBanDetail',
            BanDetail::withTrashed()
                ->where('ban_id', $banDetail->ban_id)
                ->oldest()
                ->first()
        );

        if ($data->has('evasion') && $data['evasion']) {
            $gameAdmin = $this->commonRequest->targetGameAdmin();
            $player = null;
            $ckey = $data->has('ckey') ? ckey($data['ckey']) : null;
            if ($data->has('player_id') && $data['player_id']) {
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
            $note->server_id = $ban->server_id;
            $note->round_id = $data->has('round_id') ? $data['round_id'] : null;
            $note->note = sprintf(
                'Ban evasion attempt detected, added connection details (IP: %s, CompID: %s) to ban. Original ban ckey: %s. Reason: %s',
                $banDetail->ip,
                $banDetail->comp_id,
                /** @phpstan-ignore-next-line */
                $banDetail->originalBanDetail->ckey,
                $ban->reason
            );
            $note->gameAdmin()->associate($gameAdmin);
            if ($ban->gameServer) {
                $note->gameServer()->associate($ban->gameServer);
            } elseif ($ban->gameServerGroup) {
                $note->gameServerGroup()->associate($ban->gameServerGroup);
            }
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
