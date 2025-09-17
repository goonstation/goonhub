<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ClearOldDectalks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $filePathPrefix = storage_path('app/public/dectalk');

        if (! is_dir($filePathPrefix)) {
            return;
        }

        $files = scandir($filePathPrefix);
        foreach ($files as $file) {
            $filePath = $filePathPrefix.'/'.$file;
            // Delete any mp3 files older than 1 hour
            if (is_file($filePath) && str_ends_with($filePath, '.mp3')) {
                if (time() - filemtime($filePath) > 1 * 60 * 60) {
                    unlink($filePath);
                }
            }
        }
    }
}
