<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Maintenance command for the events_logs TimescaleDB hypertable.
 *
 * This command provides visibility into:
 * - Chunk status and sizes
 * - Compression statistics
 * - Data retention information
 *
 * It can also manually trigger:
 * - Compression of eligible chunks
 */
class EventLogsMaintenance extends Command
{
    protected $signature = 'gh:events-logs-maintenance
                            {--compress : Manually compress all eligible chunks}
                            {--stats : Show detailed statistics}';

    protected $description = 'Maintain the events_logs TimescaleDB hypertable';

    public function handle(): int
    {
        $this->info('Events Logs Hypertable Maintenance');
        $this->newLine();

        if ($this->option('stats')) {
            $this->showDetailedStats();
        } else {
            $this->showBasicStats();
        }

        if ($this->option('compress')) {
            $this->compressChunks();
        }

        // if ($this->option('retention')) {
        //     $this->runRetention();
        // }

        return self::SUCCESS;
    }

    private function showBasicStats(): void
    {
        // Get hypertable info
        $hypertable = DB::selectOne("
            SELECT
                hypertable_name,
                num_chunks,
                pg_size_pretty(hypertable_size('events_logs')) as total_size
            FROM timescaledb_information.hypertables
            WHERE hypertable_name = 'events_logs'
        ");

        if (! $hypertable) {
            $this->error('events_logs is not a TimescaleDB hypertable');

            return;
        }

        $this->table(
            ['Hypertable', 'Chunks', 'Total Size'],
            [[$hypertable->hypertable_name, $hypertable->num_chunks, $hypertable->total_size]]
        );

        // Get compression stats using hypertable_compression_stats function (TimescaleDB 2.x compatible)
        $compression = DB::selectOne("
            SELECT
                COUNT(*) FILTER (WHERE is_compressed = true) as compressed_chunks,
                COUNT(*) FILTER (WHERE is_compressed = false) as uncompressed_chunks
            FROM timescaledb_information.chunks
            WHERE hypertable_name = 'events_logs'
        ");

        $compressionStats = DB::selectOne("
            SELECT
                pg_size_pretty(before_compression_total_bytes) as before_compression,
                pg_size_pretty(after_compression_total_bytes) as after_compression,
                CASE
                    WHEN before_compression_total_bytes > 0
                    THEN ROUND((1 - after_compression_total_bytes::numeric / before_compression_total_bytes::numeric) * 100, 1)
                    ELSE 0
                END as compression_ratio
            FROM hypertable_compression_stats('events_logs')
        ");

        $this->newLine();
        $this->info('Compression Status:');
        $this->table(
            ['Compressed Chunks', 'Uncompressed Chunks', 'Before Compression', 'After Compression', 'Saved'],
            [[
                $compression->compressed_chunks ?? 0,
                $compression->uncompressed_chunks ?? 0,
                $compressionStats->before_compression ?? 'N/A',
                $compressionStats->after_compression ?? 'N/A',
                $compressionStats ? ($compressionStats->compression_ratio.'%') : 'N/A',
            ]]
        );

        // Get compression and retention policies from config JSON
        $policy = DB::selectOne("
            SELECT
                config->>'compress_after' as compress_after
            FROM timescaledb_information.jobs
            WHERE hypertable_name = 'events_logs'
            AND proc_name = 'policy_compression'
        ");

        $retention = DB::selectOne("
            SELECT
                config->>'drop_after' as drop_after
            FROM timescaledb_information.jobs
            WHERE hypertable_name = 'events_logs'
            AND proc_name = 'policy_retention'
        ");

        $this->newLine();
        $this->info('Policies:');
        $this->line('  Compression: '.($policy->compress_after ?? 'Not configured'));
        $this->line('  Retention: '.($retention->drop_after ?? 'Not configured'));
    }

    private function showDetailedStats(): void
    {
        $this->showBasicStats();

        $this->newLine();
        $this->info('Chunk Details:');

        // Get chunk details
        $chunks = DB::select("
            SELECT
                chunk_name,
                range_start,
                range_end,
                is_compressed
            FROM timescaledb_information.chunks
            WHERE hypertable_name = 'events_logs'
            ORDER BY range_start DESC
            LIMIT 20
        ");

        $this->table(
            ['Chunk', 'Start', 'End', 'Compressed'],
            collect($chunks)->map(fn ($c) => [
                $c->chunk_name,
                $c->range_start,
                $c->range_end,
                $c->is_compressed ? 'Yes' : 'No',
            ])->toArray()
        );

        // Row counts
        $counts = DB::selectOne('
            SELECT
                COUNT(*) as total_rows,
                MIN(created_at) as oldest_record,
                MAX(created_at) as newest_record
            FROM events_logs
        ');

        $this->newLine();
        $this->info('Data Summary:');
        $this->line('  Total Rows: '.number_format($counts->total_rows));
        $this->line('  Oldest Record: '.($counts->oldest_record ?? 'N/A'));
        $this->line('  Newest Record: '.($counts->newest_record ?? 'N/A'));
    }

    private function compressChunks(): void
    {
        $this->newLine();
        $this->info('Compressing eligible chunks...');

        // Find uncompressed chunks that are old enough
        $result = DB::selectOne("
            SELECT compress_chunk(c.chunk_name::regclass)
            FROM timescaledb_information.chunks c
            WHERE c.hypertable_name = 'events_logs'
            AND c.is_compressed = false
            AND c.range_end < NOW() - INTERVAL '7 days'
        ");

        if ($result) {
            $this->info('Compression complete.');
        } else {
            $this->line('No chunks eligible for compression.');
        }
    }

    // private function runRetention(): void
    // {
    //     $this->newLine();
    //     $this->info('Running retention policy...');

    //     $before = DB::selectOne("
    //         SELECT COUNT(*) as count FROM timescaledb_information.chunks
    //         WHERE hypertable_name = 'events_logs'
    //     ");

    //     // Drop chunks older than retention period
    //     DB::statement("SELECT drop_chunks('events_logs', older_than => INTERVAL '3 months')");

    //     $after = DB::selectOne("
    //         SELECT COUNT(*) as count FROM timescaledb_information.chunks
    //         WHERE hypertable_name = 'events_logs'
    //     ");

    //     $dropped = $before->count - $after->count;
    //     $this->info("Dropped {$dropped} old chunk(s).");
    // }
}
