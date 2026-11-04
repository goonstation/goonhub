<?php

namespace App\Console\Commands;

use App\Services\FallbackExport;
use Illuminate\Console\Command;
use InvalidArgumentException;
use Throwable;

class ExportFallback extends Command
{
    protected $signature = 'gh:export-fallback {server_id : Textual game-server identifier} {output : Destination SQLite file path}';

    protected $description = 'Export a read-only fallback snapshot for one game server';

    public function handle(FallbackExport $export): int
    {
        try {
            $result = $export->export((string) $this->argument('server_id'), (string) $this->argument('output'));
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        } catch (Throwable) {
            // Database exceptions can contain identifiers and bound player data.
            $this->error('Fallback export failed during generation or publication. The previous snapshot was preserved.');

            return self::FAILURE;
        }
        $this->info('Published: '.$result['destination']);
        $this->line('Snapshot UTC: '.$result['snapshot_at']);
        foreach ($result['counts'] as $table => $count) {
            $this->line($table.': '.$count);
        }

        return self::SUCCESS;
    }
}
