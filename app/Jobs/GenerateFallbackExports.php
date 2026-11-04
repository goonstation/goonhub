<?php

namespace App\Jobs;

use App\Services\FallbackExport;
use App\Services\FallbackUpload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class GenerateFallbackExports implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $timeout = 300;

    public int $tries = 1;

    public bool $failOnTimeout = true;

    public int $uniqueFor = 600;

    /** @return array<int, WithoutOverlapping> */
    public function middleware(): array
    {
        return [(new WithoutOverlapping)->dontRelease()->expireAfter(360)];
    }

    public function handle(FallbackExport $export, FallbackUpload $upload): void
    {
        $directory = storage_path('app/fallback-exports');
        File::ensureDirectoryExists($directory, 0700);

        $servers = DB::connection('pgsql')->table('game_servers')
            ->where('active', true)
            ->orderBy('id')
            ->get(['server_id', 'overseer']);

        $failures = 0;
        foreach ($servers as $server) {
            $serverId = $server->server_id;
            try {
                $filename = rawurlencode($serverId).'.sqlite';
                $export->export($serverId, $directory.'/'.$filename);
            } catch (Throwable) {
                // Exception messages may contain player identifiers or SQL bindings.
                Log::error('Fallback export failed for game server.', ['server_id' => $serverId]);
                $failures++;
            }
        }

        if ($failures > 0) {
            throw new RuntimeException('Fallback export failed for '.$failures.' active game server(s). See logs for server identifiers.');
        }

        $upload->upload($directory, $servers->map(fn ($server) => [
            'server_id' => $server->server_id,
            'overseer' => $server->overseer,
        ])->all());
    }
}
