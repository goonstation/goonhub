<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\CreateColumnstorePolicy;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\CreateHypertable;
// use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\CreateRetentionPolicy;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\EnableColumnstore;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

/**
 * Converts the events_logs table to a TimescaleDB hypertable with:
 * - Automatic time-based chunking (1 week per chunk)
 * - Columnstore compression after 7 days (90%+ storage reduction)
 * - Full-text search via tsvector + GIN index
 * - 3-month data retention policy (configurable)
 *
 * This migration:
 * 1. Creates the TimescaleDB extension
 * 2. Creates a new hypertable with optimized schema
 * 3. Migrates existing data in batches
 * 4. Swaps tables atomically
 *
 * Benefits:
 * - Instant partition drops for archiving (no VACUUM FULL needed)
 * - 90%+ compression on older data
 * - Faster queries via chunk pruning
 * - GIN-indexed full-text search
 */
return new class extends Migration
{
    /**
     * Data retention period - chunks older than this will be automatically dropped.
     * Set to null to disable automatic retention.
     */
    // private const RETENTION_PERIOD = '3 months';

    /**
     * Compression delay - chunks older than this will be compressed.
     * Compressed chunks use 90%+ less storage but are slower to write to.
     */
    private const COMPRESS_AFTER = '7 days';

    /**
     * Chunk time interval - each chunk covers this time period.
     * ~8 million rows/week at your ingestion rate.
     */
    private const CHUNK_INTERVAL = '1 week';

    /**
     * Batch size for data migration to avoid locking.
     */
    private const MIGRATION_BATCH_SIZE = 50000;

    public function up(): void
    {
        // Step 1: Enable TimescaleDB extension
        Schema::createExtensionIfNotExists('timescaledb');

        // Step 2: Create the new hypertable with optimized schema
        $this->createHypertable();

        // Step 3: Migrate existing data
        $this->migrateData();

        // Step 4: Swap tables atomically
        $this->swapTables();
    }

    public function down(): void
    {
        // Drop the old backup table first to free up index/constraint names
        DB::statement('DROP TABLE IF EXISTS events_logs_old CASCADE');

        // Create standard PostgreSQL table with original schema
        Schema::create('events_logs_standard', function (Blueprint $table) {
            $table->id();
            $table->integer('round_id');
            $table->text('type')->nullable();
            $table->text('source')->nullable();
            $table->text('message')->nullable();
            $table->timestamps(precision: 3);

            $table->index('round_id');
        });

        // Migrate data from hypertable to standard table in batches
        $maxId = DB::table('events_logs')->max('id');

        if ($maxId !== null) {
            $lastId = 0;

            while ($lastId < $maxId) {
                DB::statement('
                    INSERT INTO events_logs_standard (id, round_id, type, source, message, created_at, updated_at)
                    SELECT id, round_id, type, source, message, created_at, updated_at
                    FROM events_logs
                    WHERE id > ? AND id <= ?
                    ORDER BY id
                ', [$lastId, $lastId + self::MIGRATION_BATCH_SIZE]);

                $lastId += self::MIGRATION_BATCH_SIZE;
            }
        }

        // Swap tables
        DB::transaction(function () {
            // Drop the hypertable
            DB::statement('DROP TABLE events_logs CASCADE');

            // Rename standard table to events_logs
            DB::statement('ALTER TABLE events_logs_standard RENAME TO events_logs');

            // Rename primary key index
            DB::statement('ALTER INDEX events_logs_standard_pkey RENAME TO events_logs_pkey');

            // Rename round_id index
            DB::statement('ALTER INDEX events_logs_standard_round_id_index RENAME TO events_logs_round_id_index');

            // Rename sequence
            DB::statement('ALTER SEQUENCE events_logs_standard_id_seq RENAME TO events_logs_id_seq');

            // Reset sequence to max id
            $maxId = DB::table('events_logs')->max('id') ?? 0;
            DB::statement('SELECT setval(?, ?)', ['events_logs_id_seq', $maxId]);

            // Add foreign key constraint
            DB::statement('
                ALTER TABLE events_logs
                ADD CONSTRAINT events_logs_round_id_foreign
                FOREIGN KEY (round_id) REFERENCES game_rounds(id)
            ');
        });
    }

    private function createHypertable(): void
    {
        Schema::create('events_logs_new', function (Blueprint $table) {
            // Use BIGSERIAL for id to handle high volume
            $table->bigIncrements('id');
            $table->integer('round_id');
            $table->text('type')->nullable();
            $table->text('source')->nullable();
            $table->text('message')->nullable();

            // Timestamps with millisecond precision
            $table->timestampTz('created_at', precision: 3)->useCurrent();
            $table->timestampTz('updated_at', precision: 3)->nullable();

            // Compound primary key required for hypertables (must include time column)
            // Note: We'll handle this via raw SQL since Laravel doesn't support compound PKs well
        });

        // Drop the auto-created primary key and create compound PK
        DB::statement('ALTER TABLE events_logs_new DROP CONSTRAINT events_logs_new_pkey');
        DB::statement('ALTER TABLE events_logs_new ADD PRIMARY KEY (id, created_at)');

        // Add generated tsvector column for full-text search
        DB::statement("
            ALTER TABLE events_logs_new
            ADD COLUMN search_vector tsvector
            GENERATED ALWAYS AS (
                to_tsvector('english', COALESCE(type, '') || ' ' || COALESCE(source, '') || ' ' || COALESCE(message, ''))
            ) STORED
        ");

        // Convert to hypertable with TimescaleDB settings
        Schema::table('events_logs_new', function (Blueprint $table) {
            $table->timescale(
                // Create hypertable with weekly chunks
                new CreateHypertable('created_at', self::CHUNK_INTERVAL),
                // Enable columnstore compression, segment by round_id for optimal compression
                // Include id in orderBy since it's part of the compound primary key
                new EnableColumnstore(
                    orderBy: ['created_at', 'id'],
                    segmentBy: 'round_id'
                ),
                // Automatically compress chunks older than 7 days
                new CreateColumnstorePolicy(self::COMPRESS_AFTER),
                // Automatically drop chunks older than 3 months
                // new CreateRetentionPolicy(self::RETENTION_PERIOD),
            );
        });

        // Create indexes
        // GIN index for full-text search
        DB::statement('CREATE INDEX events_logs_new_search_idx ON events_logs_new USING GIN (search_vector)');

        // Index on round_id for fast lookups by round
        DB::statement('CREATE INDEX events_logs_new_round_id_idx ON events_logs_new (round_id, created_at DESC)');

        // Index on type for filtering
        DB::statement('CREATE INDEX events_logs_new_type_idx ON events_logs_new (type, created_at DESC)');

        // Foreign key constraint
        DB::statement('
            ALTER TABLE events_logs_new
            ADD CONSTRAINT events_logs_new_round_id_fkey
            FOREIGN KEY (round_id) REFERENCES game_rounds(id)
        ');
    }

    private function migrateData(): void
    {
        $maxId = DB::table('events_logs')->max('id');

        if ($maxId === null) {
            // No data to migrate
            return;
        }

        // Migrate in batches to avoid long locks
        $lastId = 0;
        $batchCount = 0;
        $totalBatches = (int) ceil($maxId / self::MIGRATION_BATCH_SIZE);

        while ($lastId < $maxId) {
            DB::statement('
                INSERT INTO events_logs_new (id, round_id, type, source, message, created_at, updated_at)
                SELECT id, round_id, type, source, message, created_at, updated_at
                FROM events_logs
                WHERE id > ? AND id <= ?
                ORDER BY id
            ', [$lastId, $lastId + self::MIGRATION_BATCH_SIZE]);

            $lastId += self::MIGRATION_BATCH_SIZE;
            $batchCount++;

            // Log progress every 10 batches (500k rows)
            if ($batchCount % 10 === 0) {
                $percent = round(($batchCount / $totalBatches) * 100, 1);
                logger()->info("events_logs migration progress: {$percent}% ({$batchCount}/{$totalBatches} batches)");
            }

            // Small delay to reduce database load
            usleep(50000); // 50ms
        }

        // Reset sequence to continue from max id
        $newMaxId = DB::table('events_logs_new')->max('id') ?? 0;
        DB::statement("SELECT setval(pg_get_serial_sequence('events_logs_new', 'id'), ?)", [$newMaxId]);

        logger()->info("events_logs migration complete: {$batchCount} batches processed");
    }

    private function swapTables(): void
    {
        DB::transaction(function () {
            // Rename tables atomically
            DB::statement('ALTER TABLE events_logs RENAME TO events_logs_old');
            DB::statement('ALTER TABLE events_logs_new RENAME TO events_logs');

            // Rename old table's primary key and sequence to avoid conflict
            DB::statement('ALTER INDEX events_logs_pkey RENAME TO events_logs_old_pkey');
            DB::statement('ALTER SEQUENCE events_logs_id_seq RENAME TO events_logs_old_id_seq');

            // Update foreign key constraint name
            DB::statement('
                ALTER TABLE events_logs
                RENAME CONSTRAINT events_logs_new_round_id_fkey TO events_logs_round_id_fkey
            ');

            // Update index names
            DB::statement('ALTER INDEX events_logs_new_pkey RENAME TO events_logs_pkey');
            DB::statement('ALTER INDEX events_logs_new_search_idx RENAME TO events_logs_search_idx');
            DB::statement('ALTER INDEX events_logs_new_round_id_idx RENAME TO events_logs_round_id_idx');
            DB::statement('ALTER INDEX events_logs_new_type_idx RENAME TO events_logs_type_idx');

            // Update sequence name
            DB::statement('ALTER SEQUENCE events_logs_new_id_seq RENAME TO events_logs_id_seq');
        });

        logger()->info('events_logs table swap complete. Old table retained as events_logs_old.');
    }
};
