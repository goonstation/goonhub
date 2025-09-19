<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libraries\GameBuilder\Build;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;

#[Group('Game Build Artifacts')]
class GameBuildArtifactsController extends Controller
{
    private function getGameArtifacts(string $server)
    {
        $buildPath = Build::$path;
        $artifactsPath = storage_path("$buildPath/servers/$server/artifacts");

        return collect(File::allFiles($artifactsPath))
            ->filter(function ($artifact) {
                return str_starts_with($artifact->getFilename(), 'game-') && $artifact->getExtension() === 'gz';
            })
            ->sortBy(function (SplFileInfo $artifact) {
                preg_match('/^game-(\d+)\.tar\.gz$/i', $artifact->getFilename(), $matches);

                return (int) $matches[1];
            });
    }

    /**
     * Check
     *
     * Check if a buildstamp is the latest built version
     */
    public function check(Request $request)
    {
        $request->validate([
            'server' => 'required|string',
            'buildstamp' => 'required|integer',
        ]);

        $buildStamp = $request['buildstamp'];
        $artifacts = $this->getGameArtifacts($request['server']);

        $checkingArtifact = $artifacts->firstWhere(function ($artifact) use ($buildStamp) {
            return $artifact->getFilename() === "game-$buildStamp.tar.gz";
        });

        return ['latest' => $checkingArtifact === $artifacts->last()];
    }

    /**
     * Download Game
     */
    public function game(Request $request)
    {
        $request->validate([
            'server' => 'required|string',
        ]);

        $artifacts = $this->getGameArtifacts($request['server']);
        $latestArtifact = $artifacts->last();

        if (! $latestArtifact) {
            return abort(404);
        }

        return response()->download($latestArtifact);
    }

    /**
     * Download Byond
     */
    public function byond(Request $request)
    {
        $request->validate([
            'major' => 'required|integer',
            'minor' => 'required|integer',
        ]);

        $buildPath = Build::$path;
        $path = storage_path("$buildPath/byond/{$request['major']}.{$request['minor']}.tar.gz");

        if (! file_exists($path)) {
            return abort(404);
        }

        return response()->download($path);
    }

    /**
     * Download Rust-G
     */
    public function rustg(Request $request)
    {
        $request->validate([
            'version' => 'required|string',
        ]);

        $buildPath = Build::$path;
        $path = storage_path("$buildPath/rustg/{$request['version']}.zip");

        if (! file_exists($path)) {
            return abort(404);
        }

        return response()->download($path);
    }
}
