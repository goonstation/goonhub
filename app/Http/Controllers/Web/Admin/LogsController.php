<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\Permissions\LogPermissions;
use App\Helpers\HasPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\GameRounds\IndexRequest;
use App\Http\Requests\IndexQueryRequest;
use App\Models\Events\EventLog;
use App\Models\GameRound;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Inertia\Inertia;

class LogsController extends Controller implements HasMiddleware
{
    private const SEARCH_PER_PAGE = 100;

    private const SEARCH_MAX_RESULTS = 10000;

    public static function middleware(): array
    {
        return [
            HasPermission::using(LogPermissions::VIEW, only: ['index', 'show', 'getLogs', 'search', 'searchLogs', 'getLogTypes']),
        ];
    }

    public function index(IndexRequest $request)
    {
        return Inertia::render('Admin/Logs/Index', [
            // Use simple pagination to avoid expensive COUNT query on large tables.
            // The has('logs') EXISTS subquery combined with LengthAwarePaginator's COUNT
            // causes severe performance issues with millions of events_logs records.
            'rounds' => Inertia::lazy(fn () => GameRound::with([
                'server:server_id,name,short_name',
            ])
                ->withCount('logs')
                ->has('logs')
                ->indexFilterPaginate(perPage: 30, simple: true)),
        ]);
    }

    public function show(Request $request, GameRound $gameRound)
    {
        $gameRound->load([
            'server:server_id,name',
            'latestStationName:id,round_id,name',
            'mapRecord:id,map_id,name',
        ]);

        return Inertia::render('Admin/Logs/Show', [
            'round' => $gameRound,
        ]);
    }

    public function getLogs(GameRound $gameRound)
    {
        return EventLog::where('round_id', $gameRound->id)->orderBy('created_at', 'asc')->get();
    }

    /**
     * Display the search page for searching across all rounds
     */
    public function search()
    {
        return Inertia::render('Admin/Logs/Search');
    }

    /**
     * Search logs across all rounds using TimescaleDB full-text search
     */
    public function searchLogs(IndexQueryRequest $request)
    {
        $filters = $request->input('filters', []);
        $searchTerm = $filters['search'] ?? null;

        // Require a search term for cross-round search to avoid scanning all data
        if (empty($searchTerm) || strlen(trim($searchTerm)) < 3) {
            return response()->json([
                'data' => [],
                'current_page' => 1,
                'per_page' => self::SEARCH_PER_PAGE,
                'total' => 0,
                'message' => 'Please enter a search term (minimum 3 characters).',
            ]);
        }

        $query = EventLog::query()
            ->with('gameRound:id,server_id,created_at')
            ->with('gameRound.server:server_id,name,short_name')
            ->filter($filters)
            ->orderByRelevance($searchTerm)
            ->orderBy('created_at', 'desc');

        // Get total count (capped for performance)
        $totalQuery = clone $query;
        $total = min($totalQuery->count(), self::SEARCH_MAX_RESULTS);

        // Paginate results
        $perPage = min((int) $request->input('per_page', self::SEARCH_PER_PAGE), self::SEARCH_PER_PAGE);
        $page = (int) $request->input('page', 1);
        $maxPage = (int) ceil($total / $perPage);
        $page = min($page, max($maxPage, 1));

        $logs = $query
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return response()->json([
            'data' => $logs,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => $maxPage,
        ]);
    }

    /**
     * Get all distinct log types for filter dropdown
     */
    public function getLogTypes()
    {
        $types = EventLog::query()
            ->select('type')
            ->distinct()
            ->whereNotNull('type')
            ->orderBy('type')
            ->pluck('type');

        return response()->json($types);
    }
}
