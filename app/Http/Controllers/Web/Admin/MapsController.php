<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maps\IndexRequest;
use App\Jobs\BuildMap;
use App\Models\Map;
use App\Models\MapLayer;
use App\Traits\ManagesFileUploads;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;
use Pion\Laravel\ChunkUpload\Handler\AbstractHandler;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use ZipArchive;

class MapsController extends Controller
{
    use ManagesFileUploads;

    private function associateMapLayers(Map $map, ?array $layers)
    {
        if (! $map->is_layer && is_array($layers) && count($layers)) {
            MapLayer::where('map_id', $map->id)->delete();

            foreach ($layers as $layerId) {
                MapLayer::create([
                    'map_id' => $map->id,
                    'layer_id' => $layerId,
                ]);
            }
        } else {
            MapLayer::where('map_id', $map->id)->delete();
        }
    }

    private function associateBaseMaps(Map $map, ?array $baseMaps)
    {
        if ($map->is_layer && is_array($baseMaps) && count($baseMaps)) {
            MapLayer::where('layer_id', $map->id)->delete();

            foreach ($baseMaps as $baseMapId) {
                MapLayer::create([
                    'map_id' => $baseMapId,
                    'layer_id' => $map->id,
                ]);
            }
        } else {
            MapLayer::where('layer_id', $map->id)->delete();
        }
    }

    public function index(IndexRequest $request)
    {
        $maps = Map::with(['gameAdmin.player'])
            ->indexFilterPaginate(sortBy: 'name', desc: false, perPage: 30);

        if ($this->wantsInertia($request)) {
            return Inertia::render('Admin/Maps/Index', [
                'maps' => $maps,
            ]);
        } else {
            return $maps;
        }
    }

    public function showUpload()
    {
        return Inertia::render('Admin/Maps/Upload', [
            'maps' => Map::orderBy('name', 'asc')->get(),
        ]);
    }

    public function upload(Request $request)
    {
        $data = $request->validate([
            'map' => 'required|exists:maps,map_id',
            'fileName' => 'required|string',
            'filePath' => 'required|string',
        ]);

        $gameAdminId = $request->user()->gameAdmin->id;

        $finalPath = storage_path('app/'.$data['filePath'].$data['fileName']);
        $file = new File($finalPath);

        $mapZipPath = BuildMap::moveUploadedFile($file);
        $job = new BuildMap($data['map'], $mapZipPath, $gameAdminId);

        // image count check
        $zip = new ZipArchive;
        $zip->open($mapZipPath);
        $expectedImageCounts = $job->getExpectedImageCount();
        $imageCount = $zip->count();
        if ($imageCount !== $expectedImageCounts->total) {
            $job->cleanup();

            return Redirect::back()->withErrors(['error' => "Expected an archive containing {$expectedImageCounts->total} files, saw $imageCount."]);
        }
        $zip->close();

        $job->dispatch($data['map'], $mapZipPath, $gameAdminId);

        return Redirect::back();
    }

    /**
     * Handles the file upload
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws UploadMissingFileException
     */
    public function uploadFile(FileReceiver $receiver)
    {
        // check if the upload is success, throw exception or return response you need
        if ($receiver->isUploaded() === false) {
            throw new UploadMissingFileException;
        }
        // receive the file
        $save = $receiver->receive();

        // check if the upload has finished (in chunk mode it will send smaller files)
        if ($save->isFinished()) {
            // save the file and return any response you need
            $fileDetails = $this->saveFile($save->getFile());

            return response()->json([
                'path' => $fileDetails['path'],
                'name' => $fileDetails['name'],
                'mime_type' => $fileDetails['mime_type'],
            ]);
        }

        // we are in chunk mode, lets send the current progress
        /** @var AbstractHandler $handler */
        $handler = $save->handler();

        return response()->json([
            'done' => $handler->getPercentageDone(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Maps/Create', [
            'maps' => Map::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'map_id' => 'required|string|uppercase',
            'name' => 'required',
            'active' => 'required|boolean',
            'admin_only' => 'required|boolean',
            'is_layer' => 'required|boolean',
            'tile_width' => 'required|integer',
            'tile_height' => 'required|integer',
            'layers' => 'nullable|array',
            'base_maps' => 'nullable|array',
        ]);

        $map = new Map;
        $map->map_id = $data['map_id'];
        $map->name = $data['name'];
        $map->active = $data['active'];
        $map->admin_only = $data['admin_only'];
        $map->is_layer = $data['is_layer'];
        $map->tile_width = $data['tile_width'];
        $map->tile_height = $data['tile_height'];
        $map->save();

        $this->associateMapLayers($map, $data['layers']);
        $this->associateBaseMaps($map, $data['base_maps']);

        return to_route('admin.maps.index');
    }

    public function edit(Map $map)
    {
        $map->load('layers');

        $baseMaps = [];
        if ($map->is_layer) {
            $baseMaps = Map::whereHas('layers', function ($q) use ($map) {
                return $q->where('map_layers.layer_id', $map->id);
            })->pluck('id')->toArray();
        }

        return Inertia::render('Admin/Maps/Edit', [
            'map' => $map,
            'maps' => Map::orderBy('name')->get(),
            'baseMaps' => $baseMaps,
        ]);
    }

    public function update(Request $request, Map $map)
    {
        $data = $request->validate([
            'map_id' => 'required|string|uppercase',
            'name' => 'required',
            'active' => 'required|boolean',
            'admin_only' => 'required|boolean',
            'is_layer' => 'required|boolean',
            'tile_width' => 'required|integer',
            'tile_height' => 'required|integer',
            'layers' => 'nullable|array',
            'base_maps' => 'nullable|array',
        ]);

        $map->map_id = $data['map_id'];
        $map->name = $data['name'];
        $map->active = $data['active'];
        $map->admin_only = $data['admin_only'];
        $map->is_layer = $data['is_layer'];
        $map->tile_width = $data['tile_width'];
        $map->tile_height = $data['tile_height'];
        $map->save();

        $this->associateMapLayers($map, $data['layers']);
        $this->associateBaseMaps($map, $data['base_maps']);

        return to_route('admin.maps.index');
    }

    public function destroy(Map $map)
    {
        $map->delete();

        return ['message' => 'Map removed'];
    }
}
