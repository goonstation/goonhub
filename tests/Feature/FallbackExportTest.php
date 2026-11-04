<?php

namespace Tests\Feature;

use App\Jobs\GenerateFallbackExports;
use App\Services\FallbackExport;
use App\Services\FallbackUpload;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use PDO;
use RuntimeException;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class FallbackExportTest extends TestCase
{
    private Connection $source;

    private string $schema;

    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.overseer.token' => str_repeat('test', 8)]);
        Http::preventStrayRequests();
        Http::fake([]);
        $this->assertSame('testing', config('database.connections.pgsql.database'));
        $this->source = DB::connection('pgsql');
        $this->schema = 'fallback_'.bin2hex(random_bytes(6));
        $this->source->statement('CREATE SCHEMA '.$this->schema);
        $this->source->statement('SET search_path TO '.$this->schema);
        config(['database.connections.pgsql.search_path' => $this->schema]);
        $this->source->unprepared(<<<'SQL'
            CREATE TABLE game_servers (id bigserial PRIMARY KEY, server_id text, group_id bigint, active boolean NOT NULL DEFAULT false, overseer text);
            CREATE TABLE players (id bigint PRIMARY KEY, ckey text NOT NULL);
            CREATE TABLE player_admins (id bigint PRIMARY KEY, player_id bigint, alias text);
            CREATE TABLE bans (id bigint PRIMARY KEY, round_id bigint, game_admin_id bigint, server_id text,
                server_group bigint, reason text NOT NULL, requires_appeal boolean NOT NULL DEFAULT false,
                expires_at timestamp, created_at timestamp, updated_at timestamp, deleted_at timestamp);
            CREATE TABLE ban_details (id bigint PRIMARY KEY, ban_id bigint, ckey text, comp_id text, ip inet,
                player_id bigint, created_at timestamp, updated_at timestamp, deleted_at timestamp);
            CREATE TABLE player_mentors (id bigint PRIMARY KEY, player_id bigint UNIQUE, created_at timestamp, updated_at timestamp);
            CREATE TABLE player_hos (LIKE player_mentors INCLUDING ALL);
            INSERT INTO game_servers (server_id, group_id) VALUES ('main', 7), ('solo', NULL);
            SQL);
        $this->directory = sys_get_temp_dir().'/fallback-test-'.bin2hex(random_bytes(6));
        mkdir($this->directory, 0700);
    }

    protected function tearDown(): void
    {
        $this->source->unprepared('SET search_path TO public; DROP SCHEMA '.$this->schema.' CASCADE');
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->directory, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->directory);
        parent::tearDown();
    }

    private function ban(int $id, array $values = []): void
    {
        $this->source->table('bans')->insert(['id' => $id, 'reason' => 'Grief 日本語', ...$values]);
    }

    private function open(): PDO
    {
        return new PDO('sqlite:'.$this->directory.'/snapshot.sqlite', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }

    public function test_exports_scoped_active_bans_and_independent_identifiers(): void
    {
        $this->source->table('players')->insert([['id' => 12, 'ckey' => 'resolved'], ['id' => 13, 'ckey' => 'stored']]);
        $this->source->table('player_admins')->insert([['id' => 3, 'player_id' => 12, 'alias' => 'Admin'], ['id' => 4, 'player_id' => 999, 'alias' => 'Unresolved']]);
        $this->ban(1, ['game_admin_id' => 3, 'requires_appeal' => true, 'created_at' => '2026-01-02 03:04:05']);
        $this->ban(2, ['server_id' => 'main', 'expires_at' => '2099-01-01', 'game_admin_id' => 4]);
        $this->ban(3, ['server_group' => 7, 'game_admin_id' => 999]);
        $this->ban(4, ['server_id' => 'other']);
        $this->ban(5, ['server_group' => 8]);
        $this->ban(6, ['deleted_at' => '2020-01-01']);
        $this->ban(7, ['expires_at' => '2020-01-01']);
        $this->ban(8, ['server_id' => 'main', 'deleted_at' => '2020-01-01']);
        $this->ban(9, ['server_group' => 7, 'expires_at' => '2020-01-01']);
        $this->source->table('ban_details')->insert([
            ['id' => 10, 'ban_id' => 1, 'ckey' => 'stored', 'comp_id' => '000123', 'ip' => '2001:db8::1/64', 'player_id' => 12],
            ['id' => 11, 'ban_id' => 1, 'ckey' => null, 'comp_id' => null, 'ip' => '127.0.0.1', 'player_id' => 999],
            ['id' => 12, 'ban_id' => 4, 'ckey' => null, 'comp_id' => null, 'ip' => null, 'player_id' => null],
        ]);
        $this->source->table('ban_details')->insert(['id' => 13, 'ban_id' => 1, 'deleted_at' => '2020-01-01']);
        $this->source->table('ban_details')->insert(['id' => 14, 'ban_id' => 1]);
        foreach (['player_mentors', 'player_hos'] as $table) {
            $this->source->table($table)->insert(['id' => 4, 'player_id' => 12]);
        }
        $this->artisan('gh:export-fallback', ['server_id' => 'main', 'output' => $this->directory.'/snapshot.sqlite'])->assertSuccessful();
        $sqlite = $this->open();
        $this->assertSame([1, 2, 3], $sqlite->query('SELECT id FROM bans ORDER BY id')->fetchAll(PDO::FETCH_COLUMN));
        $ban = $sqlite->query('SELECT * FROM bans WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
        $this->assertSame('resolved', $ban['admin_ckey']);
        $this->assertSame('Admin', $ban['admin_alias']);
        $this->assertSame('Grief 日本語', $ban['reason']);
        $this->assertSame(1, $ban['requires_appeal']);
        $this->assertSame('2026-01-02 03:04:05', $ban['created_at']);
        $this->assertNull($ban['expires_at']);
        $this->assertNull($sqlite->query('SELECT admin_ckey FROM bans WHERE id = 2')->fetchColumn());
        $details = $sqlite->query('SELECT * FROM ban_details ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
        $this->assertCount(3, $details);
        $this->assertSame(['stored', '000123', '2001:db8::1/64', 12, 'resolved'], array_map(fn ($key) => $details[0][$key], ['ckey', 'comp_id', 'ip', 'player_id', 'player_ckey']));
        $this->assertNull($details[1]['player_ckey']);
        $this->assertSame('127.0.0.1', $details[1]['ip']);
        $this->assertNull($details[2]['ckey']);
        $this->assertNull($details[2]['player_id']);
        $this->assertNull($details[2]['player_ckey']);
        foreach (['player_mentors', 'player_hos'] as $table) {
            $this->assertSame('resolved', $sqlite->query('SELECT player_ckey FROM '.$table)->fetchColumn());
        }
        $metadata = $sqlite->query('SELECT * FROM export_metadata')->fetch(PDO::FETCH_ASSOC);
        $this->assertSame(1, $metadata['schema_version']);
        $this->assertSame('main', $metadata['server_id']);
        $this->assertSame(7, $metadata['server_group_id']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d\d-\d\d \d\d:\d\d:\d\d$/', $metadata['snapshot_at']);
        $this->assertSame('ok', $sqlite->query('PRAGMA integrity_check')->fetchColumn());
        $this->assertSame([], $sqlite->query('PRAGMA foreign_key_check')->fetchAll());
        $this->assertSame('delete', $sqlite->query('PRAGMA journal_mode')->fetchColumn());
        $this->assertCount(5, $sqlite->query("PRAGMA index_list('ban_details')")->fetchAll());
        $this->assertCount(1, $sqlite->query("PRAGMA foreign_key_list('ban_details')")->fetchAll());
        $this->assertSame(0600, fileperms($this->directory.'/snapshot.sqlite') & 0777);
        $readonly = new PDO('sqlite:file:'.$this->directory.'/snapshot.sqlite?mode=ro');
        $this->expectException(\PDOException::class);
        $readonly->exec('DELETE FROM bans');
    }

    public function test_no_group_and_reexport_replace_all_records(): void
    {
        $this->ban(1);
        $this->ban(2, ['server_group' => 7]);
        $export = new FallbackExport;
        $export->export('solo', $this->directory.'/snapshot.sqlite');
        $sqlite = $this->open();
        $this->assertSame([1], $sqlite->query('SELECT id FROM bans')->fetchAll(PDO::FETCH_COLUMN));
        $this->assertNull($sqlite->query('SELECT server_group_id FROM export_metadata')->fetchColumn());
        $this->assertSame(0, $sqlite->query('SELECT count(*) FROM player_mentors')->fetchColumn());
        $sqlite = null;
        $this->source->table('bans')->where('id', 1)->update(['deleted_at' => '2020-01-01']);
        $export->export('solo', $this->directory.'/snapshot.sqlite');
        $this->assertSame(0, $this->open()->query('SELECT count(*) FROM bans')->fetchColumn());
    }

    public function test_unknown_server_and_unresolved_membership_preserve_existing_file(): void
    {
        $path = $this->directory.'/snapshot.sqlite';
        file_put_contents($path, 'previous');
        $this->artisan('gh:export-fallback', ['server_id' => 'unknown', 'output' => $path])->assertFailed();
        $this->assertSame('previous', file_get_contents($path));
        foreach (['player_mentors', 'player_hos'] as $table) {
            $this->source->table($table)->insert(['id' => 1, 'player_id' => 999]);
            $this->artisan('gh:export-fallback', ['server_id' => 'main', 'output' => $path])->assertFailed();
            $this->assertSame('previous', file_get_contents($path));
            $this->assertSame([], glob($this->directory.'/.fallback-*'));
            $this->source->table($table)->delete();
        }
    }

    public function test_lock_contention_fails_without_changing_destination(): void
    {
        $path = $this->directory.'/snapshot.sqlite';
        file_put_contents($path, 'previous');
        $lock = fopen($path.'.lock', 'c');
        flock($lock, LOCK_EX | LOCK_NB);
        try {
            $this->artisan('gh:export-fallback', ['server_id' => 'main', 'output' => $this->directory.'/../'.basename($this->directory).'/snapshot.sqlite'])->assertFailed();
            $this->assertSame('previous', file_get_contents($path));
        } finally {
            fclose($lock);
        }
    }

    public function test_missing_destination_directory_fails(): void
    {
        $this->artisan('gh:export-fallback', ['server_id' => 'main', 'output' => $this->directory.'/missing/snapshot.sqlite'])->assertFailed();
        $this->assertDirectoryDoesNotExist($this->directory.'/missing');
    }

    public function test_batches_preserve_all_ids_and_large_text(): void
    {
        $this->source->unprepared("INSERT INTO bans (id, reason) SELECT n, repeat('長', 2000) FROM generate_series(1, 2005) n;
            INSERT INTO ban_details (id, ban_id) SELECT n, n FROM generate_series(1, 2005) n");
        $result = (new FallbackExport)->export('main', $this->directory.'/snapshot.sqlite');
        $this->assertSame(2005, $result['counts']['bans']);
        $this->assertSame(2005, $result['counts']['ban_details']);
        $this->assertSame(2005, $this->open()->query('SELECT max(id) FROM bans')->fetchColumn());
        $this->assertSame(str_repeat('長', 2000), $this->open()->query('SELECT reason FROM bans WHERE id = 1001')->fetchColumn());
    }

    public function test_generation_and_publication_failures_clean_up_and_release_lock(): void
    {
        $path = $this->directory.'/snapshot.sqlite';
        file_put_contents($path, 'previous');
        $export = new class extends FallbackExport
        {
            protected function publish(string $temporary, string $destination): void
            {
                throw new RuntimeException('Injected publication failure');
            }
        };
        try {
            $export->export('main', $path);
            $this->fail('Publication failure must propagate');
        } catch (RuntimeException $exception) {
            $this->assertSame('Injected publication failure', $exception->getMessage());
        }
        $this->assertSame('previous', file_get_contents($path));
        $this->assertSame([], glob($this->directory.'/.fallback-*'));
        $this->source->statement('ALTER TABLE bans RENAME COLUMN reason TO missing_reason');
        $this->artisan('gh:export-fallback', ['server_id' => 'main', 'output' => $path])->assertFailed();
        $this->assertSame('previous', file_get_contents($path));
        $this->assertSame([], glob($this->directory.'/.fallback-*'));
        $this->source->statement('ALTER TABLE bans RENAME COLUMN missing_reason TO reason');
        (new FallbackExport)->export('main', $path);
        $this->assertSame(0, $this->open()->query('SELECT count(*) FROM bans')->fetchColumn());
    }

    public function test_directory_permissions_and_destination_type_are_validated(): void
    {
        $path = $this->directory.'/snapshot.sqlite';
        chmod($this->directory, 0500);
        try {
            $this->artisan('gh:export-fallback', ['server_id' => 'main', 'output' => $path])->assertFailed();
        } finally {
            chmod($this->directory, 0700);
        }
        mkdir($path);
        $this->artisan('gh:export-fallback', ['server_id' => 'main', 'output' => $path])->assertFailed();
        rmdir($path);
        symlink($this->directory.'/target', $path);
        $this->artisan('gh:export-fallback', ['server_id' => 'main', 'output' => $path])->assertFailed();
        $this->assertFileDoesNotExist($this->directory.'/target');
    }

    public function test_missing_driver_fails_before_touching_destination(): void
    {
        $path = $this->directory.'/snapshot.sqlite';
        file_put_contents($path, 'previous');
        $php = [PHP_BINARY, '-n'];
        if (is_file(ini_get('extension_dir').'/pdo.'.PHP_SHLIB_SUFFIX)) {
            $php = [...$php, '-d', 'extension=pdo'];
        }
        $process = new Process([...$php, '-r',
            'require "vendor/autoload.php"; try {(new App\\Services\\FallbackExport)->export("main", $argv[1]);}
            catch (InvalidArgumentException $e) {fwrite(STDOUT, $e->getMessage()); exit(1);}', $path], base_path());
        $process->run();
        $this->assertSame(1, $process->getExitCode());
        $this->assertStringContainsString('Required PDO driver unavailable:', $process->getOutput());
        $this->assertSame('previous', file_get_contents($path));
        $this->assertFileDoesNotExist($path.'.lock');
    }

    public function test_expiry_at_cutoff_is_excluded(): void
    {
        // Shadow the database clock only in this isolated schema so equality
        // does not depend on process scheduling near a wall-clock boundary.
        $this->source->unprepared(<<<'SQL'
            CREATE FUNCTION transaction_timestamp() RETURNS timestamptz LANGUAGE SQL AS
            $$ SELECT TIMESTAMPTZ '2026-01-02 03:04:05+00' $$;
            SQL);
        config(['database.connections.pgsql.search_path' => [$this->schema, 'pg_catalog']]);
        $cutoff = '2026-01-02 03:04:05';
        $this->ban(1, ['expires_at' => $cutoff]);
        $result = (new FallbackExport)->export('main', $this->directory.'/snapshot.sqlite');
        $this->assertSame($cutoff, $result['snapshot_at']);
        $this->assertSame(0, $result['counts']['bans']);
    }

    public function test_concurrent_changes_do_not_mix_source_snapshots(): void
    {
        $this->ban(1);
        $this->source->table('players')->insert(['id' => 1, 'ckey' => 'before']);
        $this->source->table('player_mentors')->insert(['id' => 1, 'player_id' => 1]);
        $this->source->beginTransaction();
        $this->source->statement('LOCK TABLE players IN ACCESS EXCLUSIVE MODE');
        $process = new Process([PHP_BINARY, '-r',
            'require "vendor/autoload.php"; $app = require "bootstrap/app.php";
            $app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();
            config(["database.connections.pgsql.search_path" => $argv[1], "database.connections.pgsql.application_name" => $argv[1]]);
            (new App\\Services\\FallbackExport)->export("main", $argv[2]);',
            $this->schema, $this->directory.'/snapshot.sqlite'], base_path(),
            ['APP_CONFIG_CACHE' => '/tmp/fallback-no-config-cache', 'DB_DATABASE' => 'testing', 'DB_CONNECTION' => 'pgsql']);
        try {
            $process->start();
            $deadline = microtime(true) + 10;
            do {
                $this->source->select('SELECT pg_stat_clear_snapshot()');
                $waiting = $this->source->selectOne("SELECT count(*) AS count FROM pg_stat_activity WHERE application_name = ? AND wait_event_type = 'Lock'", [$this->schema])->count;
                if (! $waiting) {
                    usleep(10000);
                }
            } while (! $waiting && $process->isRunning() && microtime(true) < $deadline);
            $this->assertSame(1, (int) $waiting, $process->getErrorOutput());
            $this->source->table('bans')->where('id', 1)->update(['deleted_at' => '2020-01-01']);
            $this->source->table('players')->where('id', 1)->update(['ckey' => 'after']);
            $this->source->table('game_servers')->where('server_id', 'main')->update(['group_id' => 8]);
            $this->source->commit();
            $process->wait();
            $this->assertTrue($process->isSuccessful(), $process->getErrorOutput().$process->getOutput());
            $sqlite = $this->open();
            $this->assertSame(1, $sqlite->query('SELECT count(*) FROM bans')->fetchColumn());
            $this->assertSame('before', $sqlite->query('SELECT player_ckey FROM player_mentors')->fetchColumn());
            $this->assertSame(7, $sqlite->query('SELECT server_group_id FROM export_metadata')->fetchColumn());
        } finally {
            if ($this->source->transactionLevel() > 0) {
                $this->source->rollBack();
            }
            $process->stop();
        }
    }

    public function test_database_url_selects_the_configured_source(): void
    {
        $this->ban(1);
        $config = $this->source->getConfig();
        $url = 'postgresql://'.rawurlencode($config['username']).':'.rawurlencode($config['password']).'@'.$config['host'].':'.$config['port'].'/'.$config['database'];
        config([
            'database.connections.pgsql.url' => $url,
            'database.connections.pgsql.host' => 'invalid.example',
            'database.connections.pgsql.database' => 'invalid_database',
            'database.connections.pgsql.username' => 'invalid_user',
            'database.connections.pgsql.password' => 'invalid_password',
        ]);
        (new FallbackExport)->export('main', $this->directory.'/snapshot.sqlite');
        $this->assertSame(1, $this->open()->query('SELECT count(*) FROM bans')->fetchColumn());
    }

    public function test_job_exports_only_currently_active_servers_and_creates_directory(): void
    {
        $this->app->useStoragePath($this->directory);
        $this->source->table('game_servers')->where('server_id', 'main')->update(['active' => true]);
        $this->ban(1);
        (new GenerateFallbackExports)->handle(new FallbackExport, app(FallbackUpload::class));
        $path = $this->directory.'/app/fallback-exports';
        $this->assertFileExists($path.'/main.sqlite');
        $this->assertFileDoesNotExist($path.'/solo.sqlite');
        $db = new PDO('sqlite:file:'.$path.'/main.sqlite?mode=ro');
        $this->assertSame('main', $db->query('SELECT server_id FROM export_metadata')->fetchColumn());
        $this->assertSame(1, $db->query('SELECT count(*) FROM bans')->fetchColumn());
        $db = null;
        $this->source->table('game_servers')->where('server_id', 'solo')->update(['active' => true]);
        (new GenerateFallbackExports)->handle(new FallbackExport, app(FallbackUpload::class));
        $this->assertFileExists($path.'/solo.sqlite');
    }

    public function test_job_continues_after_one_export_fails_and_preserves_previous_snapshot(): void
    {
        $this->app->useStoragePath($this->directory);
        $this->source->table('game_servers')->update(['active' => true]);
        (new GenerateFallbackExports)->handle(new FallbackExport, app(FallbackUpload::class));
        $path = $this->directory.'/app/fallback-exports';
        $before = hash_file('sha256', $path.'/main.sqlite');
        unlink($path.'/solo.sqlite');
        $lock = fopen($path.'/main.sqlite.lock', 'c');
        flock($lock, LOCK_EX | LOCK_NB);
        try {
            (new GenerateFallbackExports)->handle(new FallbackExport, app(FallbackUpload::class));
            $this->fail('A partial failure must fail the job');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('1', $exception->getMessage());
            $this->assertSame($before, hash_file('sha256', $path.'/main.sqlite'));
            $this->assertFileExists($path.'/solo.sqlite');
        } finally {
            fclose($lock);
        }
    }

    public function test_job_encodes_server_identifiers_as_safe_filenames(): void
    {
        $this->app->useStoragePath($this->directory);
        $this->source->table('game_servers')->insert(['server_id' => '../other', 'active' => true]);
        (new GenerateFallbackExports)->handle(new FallbackExport, app(FallbackUpload::class));
        $this->assertFileExists($this->directory.'/app/fallback-exports/..%2Fother.sqlite');
        $this->assertFileDoesNotExist($this->directory.'/app/other.sqlite');
    }

    public function test_fallback_job_is_scheduled_every_five_minutes(): void
    {
        $this->app->instance('env', 'production');
        $schedule = $this->app->make(\Illuminate\Contracts\Console\Kernel::class)->resolveConsoleSchedule();
        $events = array_filter($schedule->events(), fn ($event) => $event->description === GenerateFallbackExports::class);
        $this->assertCount(1, $events);
        $this->assertSame('*/5 * * * *', array_values($events)[0]->expression);
    }

    public function test_duplicate_scheduled_jobs_are_not_queued(): void
    {
        config(['cache.default' => 'array']);
        \Illuminate\Support\Facades\Queue::fake();
        GenerateFallbackExports::dispatch();
        GenerateFallbackExports::dispatch();
        \Illuminate\Support\Facades\Queue::assertPushed(GenerateFallbackExports::class, 1);
    }

    public function test_job_middleware_prevents_concurrent_execution(): void
    {
        config(['cache.default' => 'array']);
        $job = new GenerateFallbackExports;
        $middleware = $job->middleware()[0];
        $executions = 0;
        $middleware->handle($job, function () use ($middleware, &$executions) {
            $executions++;
            $middleware->handle(new GenerateFallbackExports, function () use (&$executions) {
                $executions++;
            });
        });
        $this->assertSame(1, $executions);
        $middleware->handle(new GenerateFallbackExports, function () use (&$executions) {
            $executions++;
        });
        $this->assertSame(2, $executions);
    }

    public function test_next_export_recovers_temporary_files_after_worker_is_killed(): void
    {
        $path = $this->directory.'/snapshot.sqlite';
        file_put_contents($path, 'previous');
        $process = new Process([PHP_BINARY, '-r',
            'require "vendor/autoload.php"; $app = require "bootstrap/app.php";
            $app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();
            config(["database.connections.pgsql.search_path" => $argv[1]]);
            $export = new class extends App\\Services\\FallbackExport {
                protected function publish(string $temporary, string $destination): void {
                    posix_kill(getmypid(), SIGKILL);
                }
            };
            $export->export("main", $argv[2]);', $this->schema, $path], base_path(),
            ['APP_CONFIG_CACHE' => '/tmp/fallback-no-config-cache', 'DB_DATABASE' => 'testing', 'DB_CONNECTION' => 'pgsql']);
        try {
            $process->run();
        } catch (\Symfony\Component\Process\Exception\ProcessSignaledException) {
            // SIGKILL intentionally bypasses the exporter's cleanup.
        }
        $this->assertTrue($process->hasBeenSignaled());
        $this->assertSame('previous', file_get_contents($path));
        $orphans = glob($this->directory.'/.fallback-*');
        $this->assertCount(1, $orphans);
        file_put_contents($orphans[0].'-journal', 'abandoned journal');
        $unrelated = $this->directory.'/.fallback-'.substr(hash('sha256', $this->directory.'/other.sqlite'), 0, 32).'-other';
        file_put_contents($unrelated, 'another destination');
        (new FallbackExport)->export('main', $path);
        $this->assertSame([$unrelated], glob($this->directory.'/.fallback-*'));
        $this->assertSame('another destination', file_get_contents($unrelated));
        $this->assertSame(0, $this->open()->query('SELECT count(*) FROM bans')->fetchColumn());
    }

    public function test_job_uploads_only_after_every_active_server_export_succeeds(): void
    {
        $this->app->useStoragePath($this->directory);
        $this->source->table('game_servers')->update(['active' => true, 'overseer' => 'https://overseer.test']);
        Http::fake(function (\Illuminate\Http\Client\Request $request) {
            $this->assertFileExists($this->directory.'/app/fallback-exports/main.sqlite');
            $this->assertFileExists($this->directory.'/app/fallback-exports/solo.sqlite');

            return Http::response('', 200);
        });
        (new GenerateFallbackExports)->handle(new FallbackExport, app(FallbackUpload::class));
        Http::assertSentCount(2);
        $path = $this->directory.'/app/fallback-exports/main.sqlite.lock';
        $lock = fopen($path, 'c');
        flock($lock, LOCK_EX | LOCK_NB);
        Http::fake();
        try {
            (new GenerateFallbackExports)->handle(new FallbackExport, app(FallbackUpload::class));
            $this->fail('Generation failure must fail the job');
        } catch (RuntimeException) {
            Http::assertNothingSent();
        } finally {
            fclose($lock);
        }
    }

    private function overseerManager(): object
    {
        return new class
        {
            use \App\Traits\ManagesOverseer;

            public function status($request)
            {
                return $this->getServerStatus($request);
            }

            public function restart($request)
            {
                return $this->restartServer($request);
            }

            public function operation($request, $id)
            {
                return $this->getRestartOperation($request, $id);
            }
        };
    }

    public function test_overseer_status_restart_and_operation_use_the_shared_client(): void
    {
        config(['cache.default' => 'array']);
        $this->source->table('game_servers')->update(['active' => true, 'overseer' => 'https://overseer.test']);
        Http::fake([
            '*/status*' => Http::response(['main' => ['status' => 'running', 'health' => null, 'startedAt' => null, 'restarting' => false]]),
            '*/restart*' => Http::response(['id' => 'op-1', 'server' => 'main', 'state' => 'queued'], 202),
            '*/operations/*' => Http::response(['id' => 'op-1', 'server' => 'main', 'state' => 'succeeded']),
        ]);
        $manager = $this->overseerManager();
        $this->assertSame('running', $manager->status(\Illuminate\Http\Request::create('/'))['main']['status']);
        $request = \Illuminate\Http\Request::create('/', 'POST', ['server' => 'main']);
        $this->assertSame('queued', $manager->restart($request)['state']);
        $this->assertSame('succeeded', $manager->operation($request, 'op-1')['state']);
        Http::assertSent(fn ($request) => $request->method() === 'POST' && $request->url() === 'https://overseer.test/restart?server=main');
        foreach (Http::recorded() as [$request]) {
            $this->assertTrue($request->hasHeader('Authorization', 'Bearer '.str_repeat('test', 8)));
        }
    }

    public function test_overseer_column_migration_preserves_urls_in_both_directions(): void
    {
        $this->source->table('game_servers')->where('server_id', 'main')->update(['overseer' => 'https://overseer.test']);
        $this->source->statement('ALTER TABLE game_servers RENAME COLUMN overseer TO orchestrator');
        $migration = require database_path('migrations/2026_11_04_000001_rename_orchestrator_to_overseer_on_game_servers.php');
        $migration->up();
        $this->assertSame('https://overseer.test', $this->source->table('game_servers')->where('server_id', 'main')->value('overseer'));
        $migration->down();
        $this->assertSame('https://overseer.test', $this->source->table('game_servers')->where('server_id', 'main')->value('orchestrator'));
    }

    public function test_restart_response_provides_the_apps_polling_url(): void
    {
        config(['cache.default' => 'array']);
        $this->source->table('game_servers')->where('server_id', 'main')->update(['overseer' => 'https://overseer.test']);
        Http::fake(['*/restart*' => Http::response(['id' => 'op-1', 'server' => 'main', 'state' => 'queued', 'statusUrl' => '/operations/op-1'], 202)]);
        $controller = new \App\Http\Controllers\Api\OverseerController;
        $response = $controller->restart(\Illuminate\Http\Request::create('/', 'POST', ['server' => 'main']));
        $this->assertSame(202, $response->getStatusCode());
        $url = route('api.overseer.operation', ['id' => 'op-1', 'server' => 'main']);
        $this->assertSame($url, $response->getData(true)['statusUrl']);
        $this->assertSame($url, $response->headers->get('Location'));
    }
}
