<?php

namespace App\Jobs;

use App\Models\Map;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\File as FileFacade;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use ZipArchive;

class BuildMap implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    // If this ever changes we have bigger problems to worry about
    const TILE_SIZE = 32;

    public static $workPath = 'app/map-processing';

    private $publicMapsPath;

    private $map = null;

    private $zipPath = null;

    private $gameAdminId = null;

    private $workDir = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $map, string $zipPath, int $gameAdminId)
    {
        $mapId = preg_replace('/[^A-Za-z0-9_]/', '', $map);
        $this->map = Map::where('map_id', Str::upper($mapId))->first();
        $this->zipPath = $zipPath;
        $this->gameAdminId = $gameAdminId;
        $this->workDir = self::$workPath.'/'.Str::random(10);
        $this->publicMapsPath = $this->map->admin_only ? Map::PRIVATE_ROOT : Map::PUBLIC_ROOT;
    }

    public static function moveUploadedFile(UploadedFile|File $file)
    {
        return $file->move(storage_path(self::$workPath), Str::random(10).'.zip');
    }

    public function getExpectedImageCount()
    {
        $imagesPerRow = $this->map->tile_width / ($this->map->tile_width / 10);
        $imagesPerColumn = $this->map->tile_height / ($this->map->tile_height / 10);

        return (object) [
            'total' => $imagesPerRow * $imagesPerColumn,
            'rows' => $imagesPerRow,
            'columns' => $imagesPerColumn,
        ];
    }

    public function cleanup()
    {
        if ($this->workDir) {
            exec('rm -r "'.storage_path($this->workDir).'"');
        }
        if ($this->zipPath) {
            unlink($this->zipPath);
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (! $this->map) {
            throw new \Exception('Invalid map');
        }
        if (! $this->zipPath) {
            throw new \Exception('Invalid zip path');
        }

        ini_set('memory_limit', '1G');

        // Generate our working directories
        $workDirInput = $this->workDir.'/input';
        $workDirOutput = $this->workDir.'/output';
        mkdir(storage_path($this->workDir));
        mkdir(storage_path($workDirInput));
        mkdir(storage_path($workDirOutput));

        // Extract image files for processing
        $zip = new ZipArchive;
        $zip->open($this->zipPath);
        $zip->extractTo(storage_path($workDirInput));
        $zip->close();

        // See if we have enough images to build a map
        $inputImages = FileFacade::allFiles(storage_path($workDirInput));
        $imageCounts = $this->getExpectedImageCount();
        if (count($inputImages) < $imageCounts->total) {
            throw new \Exception('Too few images! Expected '.$imageCounts->total.' but got '.count($inputImages));
        }

        $optimizerChain = OptimizerChainFactory::create();

        // Build a canvas and generate tiles for our map
        // The canvas is for making a thumbnail of the whole thing afterwards
        $canvas = Image::create(
            $this->map->tile_width * self::TILE_SIZE,
            $this->map->tile_height * self::TILE_SIZE
        );
        $imageIndex = 0;
        for ($y = 0; $y < $imageCounts->columns; $y++) {
            for ($x = 0; $x < $imageCounts->rows; $x++) {
                $imagePath = $inputImages[$imageIndex]->getRealPath();
                $image = Image::read($imagePath);
                $gdImage = $image->core()->native();

                if (imageistruecolor($gdImage)) {
                    imagetruecolortopalette($gdImage, false, 255);
                }

                // Remove pink background color, to reduce image size
                $colorToRemove = imagecolorclosest($gdImage, 255, 0, 228); // pink, #ff00e4
                imagecolorset($gdImage, $colorToRemove, 255, 255, 255, 127);

                // Generate tile image
                $workPathImage = storage_path($workDirOutput."/$x,$y.png");
                imagepng($gdImage, $workPathImage);

                // Optimize image
                $optimizerChain->optimize($workPathImage);

                // Add this tile to our ongoing canvas
                $canvas->place(
                    $gdImage,
                    'top-left',
                    $x * (($this->map->tile_width / 10) * self::TILE_SIZE),
                    $y * (($this->map->tile_height / 10) * self::TILE_SIZE)
                );
                $imageIndex++;
            }
        }

        // Make a dinky little thumbnail of the map
        $canvas
            ->pad(200, 200, 'transparent')
            ->save(storage_path($workDirOutput.'/thumb.png'), 100);

        // Optimize thumbnail
        $optimizerChain->optimize(storage_path($workDirOutput.'/thumb.png'));

        // Make sure public output directory exists
        $mapUri = Str::lower($this->map->map_id);
        $outputPublic = $this->publicMapsPath."/$mapUri";
        if (! is_dir(storage_path($outputPublic))) {
            mkdir(storage_path($outputPublic));
        }

        // Move all our tiles and thumbnail to the right public map dir
        $files = scandir(storage_path($workDirOutput));
        $oldFolder = storage_path($workDirOutput).'/';
        $newFolder = storage_path($outputPublic).'/';
        foreach ($files as $fname) {
            if ($fname != '.' && $fname != '..') {
                rename($oldFolder.$fname, $newFolder.$fname);
            }
        }

        $this->map->last_built_at = Carbon::now();
        $this->map->last_built_by = $this->gameAdminId;
        $this->map->save();
        $this->cleanup();
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        $this->cleanup();
    }
}
