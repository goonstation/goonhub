<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maps\IndexRequest;
use App\Models\Map;
use App\Traits\IndexableQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Inertia\Inertia;

class MapsController extends Controller
{
    use IndexableQuery;

    public function index(IndexRequest $request)
    {
        $query = Map::select('id', 'map_id', 'name', 'last_built_at', 'admin_only')
            ->with([
                'latestGameRound' => function ($q) {
                    $q->where('ended_at', '!=', null)
                        ->whereRelation('server', 'invisible', false);
                },
            ])
            ->where('active', true)
            ->where('is_layer', false)
            ->orderBy('name', 'asc');

        $user = $request->user();
        if (! $user || ! $user->isGameAdmin()) {
            $query = $query->where('admin_only', false);
        }

        if ($this->wantsInertia($request)) {
            $this->setMeta(title: 'Maps', description: 'All the maps that players might encounter on their space travels');

            return Inertia::render('Maps/Index', [
                'maps' => $query->get(),
            ]);
        } else {
            $maps = $this->indexQuery(
                $query,
                perPage: 30,
                sortBy: 'name',
                desc: false
            );

            return $maps;
        }
    }

    public function show(Request $request, string $map)
    {
        $user = $request->user();
        $map = Map::select('id', 'map_id', 'name', 'tile_width', 'tile_height', 'admin_only', 'updated_at')
            ->where('map_id', Str::upper($map))
            ->where('active', true)
            ->where('is_layer', false)
            ->with([
                'layers' => function ($q) use ($user) {
                    if (! $user || ! $user->isGameAdmin()) {
                        $q->where('admin_only', false);
                    }
                },
            ]);

        if (! $user || ! $user->isGameAdmin()) {
            $map = $map->where('admin_only', false);
        }

        $map = $map->firstOrFail();
        $this->setMeta(
            title: $map->name,
            description: "Explore {$map->name}",
            image: ['type' => 'map', 'key' => $map->id]
        );

        return Inertia::render('Maps/Show', [
            'map' => $map,
        ]);
    }

    public function getPrivateTile(Request $request, string $path)
    {
        $user = $request->user();
        if (! $user || ! $user->isGameAdmin()) {
            return abort(404);
        }

        $file = storage_path(Map::PRIVATE_ROOT."/$path");

        if (File::missing($file)) {
            return abort(404);
        }

        return response()->file($file);
    }
}
