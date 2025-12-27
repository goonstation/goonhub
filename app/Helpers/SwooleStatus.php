<?php

namespace App\Helpers;

use Laravel\Octane\Swoole\ServerProcessInspector;
use Swoole\Http\Server;

class SwooleStatus
{
    public static function isRunning(): bool
    {
        return app()->bound(Server::class) && app(ServerProcessInspector::class)->serverIsRunning();
    }

    /*
    E.g.
        "start_time": 1760902796,
        "connection_num": 1,
        "abort_count": 0,
        "accept_count": 18,
        "close_count": 17,
        "worker_num": 4,
        "task_worker_num": 4,
        "user_worker_num": 0,
        "idle_worker_num": 3,
        "dispatch_count": 19,
        "request_count": 17,
        "response_count": 18,
        "total_recv_bytes": 19563,
        "total_send_bytes": 17849,
        "pipe_packet_msg_id": 55,
        "concurrency": 1,
        "session_round": 18,
        "min_fd": 34,
        "max_fd": 39,
        "worker_request_count": 0,
        "worker_response_count": 0,
        "worker_dispatch_count": 1,
        "worker_concurrency": 1,
        "task_idle_worker_num": 4,
        "tasking_num": 0,
        "task_count": 6620,
        "coroutine_num": 0,
        "coroutine_peek_num": 0
    */
    public static function getStats(): array
    {
        return app(Server::class)->stats() ?? [];
    }

    public static function getTaskWorkers(): int
    {
        $stats = self::getStats();

        return $stats['task_worker_num'];
    }

    public static function getIdleTaskWorkers(): int
    {
        $stats = self::getStats();

        return $stats['task_idle_worker_num'];
    }

    public static function getAvailableTaskWorkers(): int
    {
        $stats = self::getStats();

        return $stats['task_worker_num'] - ($stats['task_worker_num'] - $stats['task_idle_worker_num']);
    }

    public static function canDispatchTasks(): bool
    {
        return self::getAvailableTaskWorkers() > 0;
    }
}
