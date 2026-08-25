<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permissions\RandomEntryPermissions;
use App\Helpers\HasAnyAbility;
use App\Http\Controllers\Controller;
use App\Http\Resources\EventAiLawResource;
use App\Http\Resources\EventFineResource;
use App\Http\Resources\EventStationNameResource;
use App\Http\Resources\EventTicketResource;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

#[Group('Random Entries')]
class RandomEntriesController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            HasAnyAbility::using(RandomEntryPermissions::VIEW, only: ['index']),
        ];
    }

    /**
     * List
     *
     * Get a list of random entries by type
     *
     * @return array{
     *  data: array<EventTicketResource|EventFineResource|EventAiLawResource|EventStationNameResource>
     * }
     */
    public function index(Request $request)
    {
        $data = $this->validate($request, [
            'type' => ['required', Rule::in([
                'tickets',
                'fines',
                'ai_laws',
                'station_names',
            ])],
            'count' => 'numeric|between:1,100',
        ]);

        $table = 'events_'.$data['type'];
        $data = DB::table($table)
            ->inRandomOrder()
            ->limit($data['count'] ?? 10)
            ->get()
            ->toArray();

        return [
            'data' => $data,
        ];
    }
}
