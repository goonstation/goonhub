<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\BuildMap;
use App\Models\Map;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use ZipArchive;

class MapsController extends Controller
{
    /**
     * Generate
     *
     * Dispatches a job to process map screenshots and upload them to the web map viewer
     *
     * The uploaded zip file should contain images taken with the ingame Map-World verb.
     * For a regular 300x300 map, this should be 100 images.
     * The names of the individual image files should remain as what the Map-World verb names them.
     * This route is intended for out-of-game usage only.
     *
     * Example:
     *
     * ```sh
     * curl --request POST \
     *   --url {base_url}/api/maps/generate \
     *   --header 'Authorization: Bearer {bearer_token}' \
     *   --header 'Content-Type: multipart/form-data' \
     *   --form map=oshan \
     *   --form 'images=@{full_path_to_file}\Oshan Laboratory, Abzu 2023-06-15 172448.zip'
     * ```
     */
    public function generate(Request $request)
    {
        $data = $this->validate($request, [
            'map' => 'required|string',
            /** A zip file containing map screenshot images */
            'images' => 'required|file|mimes:zip',
        ]);

        $map = Map::where('map_id', Str::upper($data['map']))->first();
        if (! $map) {
            return response()->json(['message' => 'Unable to locate configuration for that map.'], 400);
        }

        $gameAdminId = $request->user()->gameAdmin->id;

        $file = $request->file('images');
        $mapZipPath = BuildMap::moveUploadedFile($file);
        $job = new BuildMap($data['map'], $mapZipPath, $gameAdminId);

        $zip = new ZipArchive;
        $zip->open($mapZipPath);
        $expectedImageCounts = $job->getExpectedImageCount();
        $imageCount = $zip->count();
        if ($imageCount !== $expectedImageCounts->total) {
            $job->cleanup();

            return response()->json(['message' => "Expected an archive containing {$expectedImageCounts->total} files, saw $imageCount."], 400);
        }
        $zip->close();

        $job->dispatch($data['map'], $mapZipPath, $gameAdminId);

        return ['message' => 'Success'];
    }
}
