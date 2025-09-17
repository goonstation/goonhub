<?php

namespace App\Http\Controllers\Web\Admin;

use App\Facades\GameBridge;
use App\Http\Controllers\Controller;
use App\Http\Requests\Bans\StoreRequest;
use App\Libraries\DiscordBot;
use App\Models\Ban;
use App\Models\BanDetail;
use App\Traits\ManagesBans;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class BansController extends Controller
{
    use ManagesBans;

    public function index(Request $request)
    {
        $bans = Ban::withCount(['details'])
            ->with(['originalBanDetail', 'gameAdmin.player', 'gameServer'])
            ->where('expires_at', '>', Carbon::now())
            ->orWhere('expires_at', null)
            ->indexFilterPaginate(perPage: 30);

        if ($this->wantsInertia($request)) {
            return Inertia::render('Admin/Bans/Index', [
                'bans' => $bans,
            ]);
        } else {
            return $bans;
        }
    }

    public function indexRemoved(Request $request)
    {
        $bans = Ban::withTrashed()
            ->withCount(['details'])
            ->with([
                'originalBanDetail',
                'gameAdmin.player',
                'gameServer',
            ])
            ->where('deleted_at', '!=', null)
            ->orWhere('expires_at', '<=', Carbon::now())
            ->indexFilterPaginate(perPage: 30);

        if ($this->wantsInertia($request)) {
            return Inertia::render('Admin/Bans/IndexRemoved', [
                'bans' => $bans,
            ]);
        } else {
            return $bans;
        }
    }

    public function create()
    {
        return Inertia::render('Admin/Bans/Create');
    }

    public function store(StoreRequest $request)
    {
        $request->merge([
            'game_admin_id' => $request->user()->gameAdmin->id,
        ]);
        $ban = $this->addBan($request);

        dispatch(function () use ($ban) {
            try {
                $byondEpochStart = Carbon::parse('2000-01-01 00:00:00'); // byond epoch start
                DiscordBot::export('ban', 'GET', [
                    'key' => $ban->gameAdmin->player->ckey,
                    'key2' => "{$ban->originalBanDetail->ckey} (IP: {$ban->originalBanDetail->ip}, CompID: {$ban->originalBanDetail->comp_id})",
                    'msg' => $ban->reason,
                    'time' => $ban->expires_at ? $ban->duration_human : 'permanent',
                    'timestamp' => $ban->expires_at ? $ban->expires_at->diffInMinutes($byondEpochStart) : 0,
                ]);
            } catch (\Throwable $e) {
                // ignore
            }

            GameBridge::create()
                ->target($ban->server_id ?: 'active')
                ->message([
                    'type' => 'ban_added',
                    'admin_ckey' => $ban->gameAdmin->player->ckey,
                    'server_id' => $ban->server_id,
                    'ckey' => $ban->originalBanDetail->ckey,
                    'comp_id' => $ban->originalBanDetail->comp_id,
                    'ip' => $ban->originalBanDetail->ip,
                    'reason' => $ban->reason,
                    'duration' => $ban->duration,
                    'requires_appeal' => $ban->requires_appeal ? 1 : 0,
                ])
                ->force(true)
                ->sendAndForget();
        });

        return to_route('admin.bans.index');
    }

    public function edit(Ban $ban)
    {
        $ban->load('originalBanDetail');

        return Inertia::render('Admin/Bans/Edit', [
            'ban' => $ban,
        ]);
    }

    public function update(StoreRequest $request, Ban $ban)
    {
        try {
            $request = $request->merge([
                'game_admin_id' => $request->user()->gameAdmin->id,
            ]);
            $this->updateBan($request, $ban);
        } catch (\Exception $e) {
            return Redirect::back()->withErrors(['error' => $e->getMessage()]);
        }

        return to_route('admin.bans.index');
    }

    public function show(int $ban)
    {
        $ban = Ban::withTrashed()
            ->with([
                'originalBanDetail',
                'gameAdmin.player',
                'deletedByGameAdmin.player',
                'gameServer',
                'details' => function ($q) {
                    $q->where('deleted_at', null)
                        ->orderBy('id', 'desc');
                },
            ])
            ->findOrFail($ban);

        return Inertia::render('Admin/Bans/Show', [
            'ban' => $ban,
        ]);
    }

    public function destroy(Request $request, Ban $ban)
    {
        $ban->deleted_by = $request->user()->gameAdmin->id;
        $ban->save();
        $ban->delete();

        return ['message' => 'Ban removed'];
    }

    public function destroyMulti(Request $request)
    {
        $data = $this->validate($request, [
            'ids' => 'required|array',
        ]);

        $bans = Ban::whereIn('id', $data['ids']);
        $bans->update(['deleted_by' => $request->user()->gameAdmin->id]);
        $bans->delete();

        return ['message' => 'Bans removed'];
    }

    public function getDetails(Request $request)
    {
        $details = BanDetail::withTrashed()
            ->where('ban_id', $request->input('ban_id'))
            ->orderBy('id', 'desc')
            ->get();

        $originalDetail = $details->last();
        $details = $details->filter(function (BanDetail $detail) use ($originalDetail) {
            return ! $detail->trashed() || $detail->id === $originalDetail->id;
        });

        return $details->values();
    }

    public function storeDetail(Request $request, Ban $ban)
    {
        $data = $this->validate($request, [
            'ckey' => 'required_without_all:comp_id,ip|nullable',
            'comp_id' => 'required_without_all:ckey,ip|nullable',
            'ip' => 'required_without_all:ckey,comp_id|nullable|ip',
        ]);

        $banDetail = new BanDetail;
        $banDetail->ckey = isset($data['ckey']) ? $data['ckey'] : null;
        $banDetail->comp_id = isset($data['comp_id']) ? $data['comp_id'] : null;
        $banDetail->ip = isset($data['ip']) ? $data['ip'] : null;
        $ban->details()->save($banDetail);

        return ['data' => $banDetail];
    }

    public function updateDetail(Request $request, BanDetail $banDetail)
    {
        $data = $this->validate($request, [
            'ckey' => 'required_without_all:comp_id,ip|nullable',
            'comp_id' => 'required_without_all:ckey,ip|nullable',
            'ip' => 'required_without_all:ckey,comp_id|nullable|ip',
        ]);

        $banDetail->ckey = isset($data['ckey']) ? $data['ckey'] : null;
        $banDetail->comp_id = isset($data['comp_id']) ? $data['comp_id'] : null;
        $banDetail->ip = isset($data['ip']) ? $data['ip'] : null;
        $banDetail->save();

        return ['data' => $banDetail];
    }

    public function destroyDetail(BanDetail $banDetail)
    {
        $banDetail->delete();

        return ['message' => 'Ban detail removed'];
    }

    public function showRemoveDetails(Request $request)
    {
        $data = [
            'lookupFields' => [
                'ckey' => $request->input('ckey', null),
                'comp_id' => $request->input('comp_id', null),
                'ip' => $request->input('ip', null),
            ],
        ];

        $session = $request->session();
        if ($session->has('lookup') && $session->has('lookupFields')) {
            $data['lookup'] = $session->get('lookup');
            $data['lookupFields'] = $session->get('lookupFields');
        }

        return Inertia::render('Admin/Bans/RemoveDetails', $data);
    }

    public function lookupDetails(Request $request)
    {
        $data = $this->validate($request, [
            'ckey' => 'required_without_all:comp_id,ip|nullable',
            'comp_id' => 'required_without_all:ckey,ip|nullable',
            'ip' => 'required_without_all:ckey,comp_id|nullable|ip',
        ]);

        $conditions = function ($query) use ($data) {
            $query->where(function ($q) use ($data) {
                if (isset($data['ckey']) && $data['ckey']) {
                    $q->orWhere('ckey', $data['ckey']);
                }
                if (isset($data['comp_id']) && $data['comp_id']) {
                    $q->orWhere('comp_id', $data['comp_id']);
                }
                if (isset($data['ip']) && $data['ip']) {
                    $q->orWhere('ip', $data['ip']);
                }
            });
        };

        $bans = Ban::with([
            'details' => $conditions,
            'originalBanDetail',
            'gameAdmin',
            'gameServer',
        ])
            ->withCount('details')
            ->whereHas('details', $conditions)
            ->orderBy('id', 'desc')
            ->get();

        $bans = $bans->where('active', true);

        return redirect(route('admin.bans.show-remove-details'))->with([
            'lookup' => [...$bans],
            'lookupFields' => $data,
        ]);
    }

    public function removeLookupDetails(Request $request)
    {
        $data = $this->validate($request, [
            'details' => 'required|array',
            'fields' => 'required|array',
            'fields.ckey' => 'nullable|string',
            'fields.comp_id' => 'nullable|string',
            'fields.ip' => 'nullable|ip',
        ]);

        foreach ($data['details'] as $detailId) {
            $banDetail = BanDetail::where('id', $detailId)->first();
            $willHave = [
                'ckey' => (bool) $banDetail->ckey,
                'comp_id' => (bool) $banDetail->comp_id,
                'ip' => (bool) $banDetail->ip,
            ];

            if ($data['fields']['ckey'] === $banDetail->ckey) {
                $willHave['ckey'] = false;
            }
            if ($data['fields']['comp_id'] === $banDetail->comp_id) {
                $willHave['comp_id'] = false;
            }
            if ($data['fields']['ip'] === $banDetail->ip) {
                $willHave['ip'] = false;
            }

            // @phpstan-ignore argument.type
            $deleting = empty(array_filter(array_values($willHave), 'strlen'));

            if ($deleting) {
                // Deleting ban detail
                $ban = Ban::where('id', $banDetail->ban_id)
                    ->with('originalBanDetail')
                    ->first();

                if ($ban->originalBanDetail->id === $banDetail->id) {
                    // This ban detail is the original ban detail for this ban
                    // So delete the actual ban record too
                    $ban->deleted_by = $request->user()->gameAdmin->id;
                    $ban->save();
                    $ban->delete();
                }

                $banDetail->delete();

            } else {
                // Editing ban detail
                if (! $willHave['ckey']) {
                    $banDetail->ckey = null;
                }
                if (! $willHave['comp_id']) {
                    $banDetail->comp_id = null;
                }
                if (! $willHave['ip']) {
                    $banDetail->ip = null;
                }
                $banDetail->save();
            }
        }

        return true;
    }
}
