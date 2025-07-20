<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GameBuildTestMergeCreateRequest;
use App\Models\GameBuildTestMerge;
use App\Traits\ManagesGameBuildTestMerges;
use Github\ResultPager;
use GrahamCampbell\GitHub\Facades\GitHub;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class GameBuildTestMergesController extends Controller
{
    use ManagesGameBuildTestMerges;

    private function getTestMergeGroups()
    {
        return GameBuildTestMerge::with([
            'buildSettings',
            'buildSettings.gameServer',
            'addedBy',
            'updatedBy',
        ])
            ->get()
            ->groupBy('pr_id')
            ->map(function (Collection $item) {
                return $item->sortBy('buildSettings.gameServer.id')->values()->all();
            });
    }

    private function getPullRequestDetails(int $prId)
    {
        try {
            /** @var \Github\Client */
            $conn = GitHub::connection();
            $item = $conn->pullRequest()->show(
                config('goonhub.github_organization'),
                config('goonhub.github_repo'),
                $prId
            );

            return [
                'number' => $item['number'],
                'merged' => $item['merged'],
                'locked' => $item['locked'],
                'draft' => $item['draft'],
                'title' => $item['title'],
                'user' => [
                    'login' => $item['user']['login'],
                    'html_url' => $item['user']['html_url'],
                ],
                'head' => [
                    'sha' => $item['head']['sha'],
                ],
                'created_at' => $item['created_at'],
                'updated_at' => $item['updated_at'],
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    private function getPullRequestList()
    {
        try {
            /** @var \Github\Client */
            $conn = GitHub::connection();
            $pagination = new ResultPager($conn);
            $data = $pagination->fetchAll($conn->pullRequests(), 'all', [
                config('goonhub.github_organization'),
                config('goonhub.github_repo'),
                ['state' => 'open'],
            ]);

            $items = collect();
            foreach ($data as $item) {
                $items->add([
                    'number' => $item['number'],
                    'locked' => $item['locked'],
                    'draft' => $item['draft'],
                    'title' => $item['title'],
                    'user' => [
                        'login' => $item['user']['login'],
                        'html_url' => $item['user']['html_url'],
                    ],
                    'head' => [
                        'sha' => $item['head']['sha'],
                    ],
                    'created_at' => $item['created_at'],
                    'updated_at' => $item['updated_at'],
                ]);
            }

            return $items;
        } catch (\Throwable) {
            return null;
        }
    }

    public function pullRequestDetails(int $prId)
    {
        $details = Cache::remember("pr-details-$prId", 300, fn () => $this->getPullRequestDetails($prId));
        if (! $details) {
            return abort(503, 'Failed to fetch pull request details from GitHub, please try again.');
        }

        return $details;
    }

    public function pullRequests()
    {
        $details = Cache::remember('pr-list', 300, fn () => $this->getPullRequestList());
        if (! $details) {
            return abort(503, 'Failed to fetch pull request details from GitHub, please try again.');
        }

        return $details;
    }

    public function index()
    {
        return Inertia::render('Admin/GameBuilds/TestMerges/Index', [
            'pullRequests' => $this->getTestMergeGroups(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/GameBuilds/TestMerges/Create', [
            'pullRequests' => $this->getTestMergeGroups(),
        ]);
    }

    public function store(GameBuildTestMergeCreateRequest $request)
    {
        $request->merge([
            'game_admin_ckey' => $request->user()->gameAdmin->ckey,
        ]);

        try {
            $this->addTestMerge($request);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return to_route('admin.builds.test-merges.index');
    }

    public function editCommit(GameBuildTestMerge $testMerge)
    {
        return Inertia::render('Admin/GameBuilds/TestMerges/UpdateCommit', [
            'pullRequests' => $this->getTestMergeGroups(),
            'testMerge' => $testMerge,
        ]);
    }

    public function updateCommit(Request $request, GameBuildTestMerge $testMerge)
    {
        $request->validate([
            'commit' => 'required|string',
        ]);

        if ($request['commit'] !== $testMerge->commit) {
            $testMerge->commit = $request['commit'];
            $testMerge->updatedBy()->associate($request->user()->gameAdmin);
            $testMerge->save();
        }

        return to_route('admin.builds.test-merges.index');
    }

    public function updateCommits(Request $request, int $prId)
    {
        $details = Cache::remember(
            "pr-details-$prId",
            300,
            fn () => $this->getPullRequestDetails($prId)
        );

        if (! $details) {
            return abort(503, 'Failed to fetch details from GitHub, please try again.');
        }

        $testMerges = GameBuildTestMerge::where('pr_id', $prId)->get();

        foreach ($testMerges as $testMerge) {
            if ($details['head']['sha'] !== $testMerge->commit) {
                $testMerge->commit = $details['head']['sha'];
                $testMerge->updatedBy()->associate($request->user()->gameAdmin);
                $testMerge->save();
            }
        }

        return ['message' => 'Test merge commits updated'];
    }

    public function destroy(GameBuildTestMerge $testMerge)
    {
        $this->destroyTestMerge($testMerge);

        return ['message' => 'Test merge removed'];
    }

    public function destroyMulti(Request $request)
    {
        $data = $this->validate($request, [
            'ids' => 'required|array',
        ]);

        $testMerges = GameBuildTestMerge::whereIn('id', $data['ids'])->get();
        foreach ($testMerges as $testMerge) {
            $this->destroyTestMerge($testMerge);
        }

        return ['message' => 'Test merges removed'];
    }
}
