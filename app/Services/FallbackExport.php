<?php

namespace App\Services;

use DirectoryIterator;
use Illuminate\Database\ConfigurationUrlParser;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use PDO;
use RuntimeException;

class FallbackExport
{
    /** @return array{destination: string, snapshot_at: string, counts: array<string, int>} */
    public function export(string $serverId, string $output): array
    {
        foreach (['pgsql', 'sqlite'] as $driver) {
            if (! in_array($driver, PDO::getAvailableDrivers(), true)) {
                throw new InvalidArgumentException('Required PDO driver unavailable: '.$driver);
            }
        }
        if ($serverId === '' || $output === '' || str_contains($output, "\0")) {
            throw new InvalidArgumentException('A server identifier and destination file path are required.');
        }
        $directory = realpath(dirname($output));
        if ($directory === false || ! is_dir($directory) || ! is_writable($directory)) {
            throw new InvalidArgumentException('Destination directory must exist and be writable.');
        }
        $destination = $directory.DIRECTORY_SEPARATOR.basename($output);
        if (is_link($destination) || (file_exists($destination) && ! is_file($destination))) {
            throw new InvalidArgumentException('Destination must be a regular file, not a directory or symlink.');
        }

        // Keep this inode: unlinking the lock would allow overlapping exporters.
        $oldMask = umask(0077);
        try {
            $lock = @fopen($destination.'.lock', 'c');
        } finally {
            umask($oldMask);
        }
        if ($lock === false) {
            throw new InvalidArgumentException('Cannot open the destination lock.');
        }
        $temporary = null;
        $sqlite = null;
        $source = null;
        $connection = null;
        try {
            if (! flock($lock, LOCK_EX | LOCK_NB)) {
                throw new InvalidArgumentException('Another export is already writing this destination.');
            }
            // The destination lock makes artifacts with this prefix safe to reclaim
            // after a previous worker was killed without running finally.
            $prefix = '.fallback-'.substr(hash('sha256', $destination), 0, 32).'-';
            foreach (new DirectoryIterator($directory) as $artifact) {
                if (str_starts_with($artifact->getFilename(), $prefix) && $artifact->isFile()) {
                    unlink($artifact->getPathname());
                }
            }
            $temporary = tempnam($directory, $prefix);
            if ($temporary === false || dirname($temporary) !== $directory || ! chmod($temporary, 0600)) {
                throw new RuntimeException('Cannot create private temporary snapshot.');
            }
            /** @var Connection $connection */
            $connection = DB::build((new ConfigurationUrlParser)->parseConfiguration(config('database.connections.pgsql')));
            $source = $connection->getPdo();
            $source->beginTransaction();
            $source->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ, READ ONLY');
            $source->exec("SET LOCAL TIME ZONE 'UTC'");
            $snapshotAt = $source->query("SELECT to_char(transaction_timestamp() AT TIME ZONE 'UTC', 'YYYY-MM-DD HH24:MI:SS')")->fetchColumn();
            $serverQuery = $source->prepare('SELECT group_id FROM game_servers WHERE server_id = ?');
            $serverQuery->execute([$serverId]);
            $server = $serverQuery->fetch(PDO::FETCH_ASSOC);
            $serverQuery = null;
            if ($server === false) {
                throw new InvalidArgumentException('Unknown game server identifier.');
            }
            $groupId = $server['group_id'];
            $sqlite = new PDO('sqlite:'.$temporary, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $sqlite->exec('PRAGMA journal_mode=DELETE; PRAGMA foreign_keys=ON');
            $sqlite->beginTransaction();
            $this->createSchema($sqlite);

            $scope = 'b.deleted_at IS NULL AND (b.expires_at IS NULL OR b.expires_at > :snapshot)
                AND ((b.server_id IS NULL AND b.server_group IS NULL) OR b.server_id = :server';
            $parameters = ['snapshot' => $snapshotAt, 'server' => $serverId];
            if ($groupId !== null) {
                $scope .= ' OR b.server_group = :group_id';
                $parameters['group_id'] = $groupId;
            }
            $scope .= ')';
            $counts = [];
            $counts['bans'] = $this->copy($source, $sqlite, 'bans', 'b.id',
                'SELECT b.id, b.round_id, b.game_admin_id, b.server_id, b.server_group, b.reason,
                    CASE WHEN b.requires_appeal THEN 1 ELSE 0 END AS requires_appeal, '.
                    $this->timestamps('b', ['expires_at', 'created_at', 'updated_at']).',
                    p.ckey AS admin_ckey, a.alias AS admin_alias
                FROM bans b LEFT JOIN player_admins a ON a.id = b.game_admin_id
                LEFT JOIN players p ON p.id = a.player_id WHERE '.$scope, $parameters);
            $counts['ban_details'] = $this->copy($source, $sqlite, 'ban_details', 'd.id',
                'SELECT d.id, d.ban_id, d.ckey, d.comp_id,
                    CASE WHEN masklen(d.ip) = CASE family(d.ip) WHEN 4 THEN 32 ELSE 128 END
                        THEN host(d.ip) ELSE d.ip::text END AS ip,
                    d.player_id, '.$this->timestamps('d', ['created_at', 'updated_at']).', p.ckey AS player_ckey
                FROM ban_details d JOIN bans b ON b.id = d.ban_id
                LEFT JOIN players p ON p.id = d.player_id
                WHERE d.deleted_at IS NULL AND '.$scope, $parameters);
            foreach (['player_mentors', 'player_hos'] as $table) {
                $counts[$table] = $this->copy($source, $sqlite, $table, 'm.id',
                    'SELECT m.id, m.player_id, '.$this->timestamps('m', ['created_at', 'updated_at']).', p.ckey AS player_ckey
                    FROM '.$table.' m LEFT JOIN players p ON p.id = m.player_id WHERE TRUE', []);
            }
            $insert = $sqlite->prepare('INSERT INTO export_metadata VALUES (1, ?, ?, ?)');
            $insert->execute([$serverId, $groupId, $snapshotAt]);
            $insert = null;
            $sqlite->commit();
            foreach ($counts as $table => $count) {
                if ((int) $sqlite->query('SELECT count(*) FROM '.$table)->fetchColumn() !== $count) {
                    throw new RuntimeException('Snapshot row count validation failed.');
                }
            }
            if ($sqlite->query('PRAGMA integrity_check')->fetchAll(PDO::FETCH_COLUMN) !== ['ok'] ||
                $sqlite->query('PRAGMA foreign_key_check')->fetchAll() !== []) {
                throw new RuntimeException('Snapshot integrity validation failed.');
            }
            $source->commit();
            $sqlite = null;
            $source = null;
            $connection->disconnect();
            $this->publish($temporary, $destination);
            $temporary = null;

            return ['destination' => $destination, 'snapshot_at' => $snapshotAt, 'counts' => $counts];
        } finally {
            if ($sqlite !== null && $sqlite->inTransaction()) {
                $sqlite->rollBack();
            }
            if ($source !== null && $source->inTransaction()) {
                $source->rollBack();
            }
            $sqlite = null;
            $source = null;
            $connection?->disconnect();
            if (is_string($temporary)) {
                foreach ([$temporary, $temporary.'-journal', $temporary.'-wal', $temporary.'-shm'] as $file) {
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }
            }
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    protected function publish(string $temporary, string $destination): void
    {
        if (! @rename($temporary, $destination)) {
            throw new RuntimeException('Cannot publish snapshot.');
        }
    }

    /** @param array<string> $columns */
    private function timestamps(string $alias, array $columns): string
    {
        // Application timestamp-without-time-zone columns represent UTC.
        return implode(', ', array_map(fn ($column) => "to_char({$alias}.{$column}, 'YYYY-MM-DD HH24:MI:SS') AS {$column}", $columns));
    }

    /** @param array<string, mixed> $parameters */
    private function copy(PDO $source, PDO $sqlite, string $table, string $id, string $select, array $parameters): int
    {
        $query = $source->prepare($select.' AND '.$id.' > :last_id ORDER BY '.$id.' ASC LIMIT 1000');
        $lastId = 0;
        $count = 0;
        $insert = null;
        do {
            $query->execute([...$parameters, 'last_id' => $lastId]);
            $rows = $query->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                if (in_array($table, ['player_mentors', 'player_hos'], true) && $row['player_ckey'] === null) {
                    throw new InvalidArgumentException('Unresolved required player relationship in '.$table.'.');
                }
                if ($insert === null) {
                    $insert = $sqlite->prepare('INSERT INTO '.$table.' ('.implode(', ', array_keys($row)).') VALUES ('.implode(', ', array_fill(0, count($row), '?')).')');
                }
                foreach (array_values($row) as $index => $value) {
                    $insert->bindValue($index + 1, $value, $value === null ? PDO::PARAM_NULL : (is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR));
                }
                $insert->execute();
                $lastId = $row['id'];
                $count++;
            }
        } while (count($rows) === 1000);

        return $count;
    }

    private function createSchema(PDO $sqlite): void
    {
        $sqlite->exec(<<<'SQL'
            CREATE TABLE bans (
                id INTEGER PRIMARY KEY, round_id INTEGER, game_admin_id INTEGER, server_id TEXT, server_group INTEGER,
                reason TEXT NOT NULL, requires_appeal INTEGER NOT NULL CHECK (requires_appeal IN (0, 1)),
                expires_at TEXT, created_at TEXT, updated_at TEXT, admin_ckey TEXT, admin_alias TEXT
            );
            CREATE TABLE ban_details (
                id INTEGER PRIMARY KEY, ban_id INTEGER NOT NULL REFERENCES bans(id), ckey TEXT, comp_id TEXT,
                ip TEXT, player_id INTEGER, created_at TEXT, updated_at TEXT, player_ckey TEXT
            );
            CREATE INDEX ban_details_ban_id ON ban_details(ban_id);
            CREATE INDEX ban_details_ckey ON ban_details(ckey);
            CREATE INDEX ban_details_comp_id ON ban_details(comp_id);
            CREATE INDEX ban_details_player_id ON ban_details(player_id);
            CREATE INDEX ban_details_ip ON ban_details(ip);
            CREATE TABLE player_mentors (
                id INTEGER PRIMARY KEY, player_id INTEGER NOT NULL UNIQUE, created_at TEXT, updated_at TEXT, player_ckey TEXT NOT NULL
            );
            CREATE INDEX player_mentors_player_ckey ON player_mentors(player_ckey);
            CREATE TABLE player_hos (
                id INTEGER PRIMARY KEY, player_id INTEGER NOT NULL UNIQUE, created_at TEXT, updated_at TEXT, player_ckey TEXT NOT NULL
            );
            CREATE INDEX player_hos_player_ckey ON player_hos(player_ckey);
            CREATE TABLE export_metadata (
                schema_version INTEGER NOT NULL CHECK (schema_version = 1), server_id TEXT NOT NULL,
                server_group_id INTEGER, snapshot_at TEXT NOT NULL
            );
            SQL);
    }
}
