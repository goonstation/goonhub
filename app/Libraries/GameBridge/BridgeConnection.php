<?php

namespace App\Libraries\GameBridge;

use App\Models\GameServer;
use App\Models\Scopes\GameServerGroupScope;
use Illuminate\Database\Eloquent\Collection as ModelCollection;
use Illuminate\Support\Collection;
use RuntimeException;

class BridgeConnection
{
    private Collection $targets;

    private int $timeout = 5;

    private ?string $message = null;

    private bool $force = false;

    private int $cacheFor = 30;

    public function target($target)
    {
        if (! $target) {
            throw new RuntimeException('Invalid target');
        }

        $this->targets = collect();
        $servers = new ModelCollection;

        if ($target instanceof GameServer) {
            $servers->add($target);
        } elseif ($target instanceof ModelCollection) {
            $servers = $target;
        } else {
            $query = GameServer::withoutGlobalScope(GameServerGroupScope::class)
                ->select(['server_id', 'address', 'port']);

            if (is_string($target)) {
                if ($target === 'active') {
                    $query = $query->where('active', true);
                } elseif ($target !== 'all') {
                    $query = $query->where('server_id', $target);
                }
            } elseif (is_array($target)) {
                $query = $query->whereIn('server_id', $target);
            }

            $servers = $query->get();
        }

        /** @var GameServer $server */
        foreach ($servers as $server) {
            $this->targets->add((object) [
                'serverId' => $server->server_id,
                'address' => $server->address,
                'port' => $server->port,
            ]);
        }

        return $this;
    }

    public function timeout(int $timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function message(string|array $message)
    {
        if (is_array($message)) {
            $message = http_build_query($message);
        }
        if (! str_starts_with($message, '?')) {
            $message = "?$message";
        }
        $this->message = $message;

        return $this;
    }

    public function force(bool $force)
    {
        $this->force = $force;

        return $this;
    }

    public function cacheFor(int $seconds)
    {
        $this->cacheFor = $seconds;

        return $this;
    }

    private function handler($address, $port, $options)
    {
        $parentSpan = \Sentry\SentrySdk::getCurrentHub()->getSpan();
        $span = null;

        if ($parentSpan !== null) {
            $span = $parentSpan->startChild(\Sentry\Tracing\SpanContext::make()->setOp('game_bridge'));
            \Sentry\SentrySdk::getCurrentHub()->setSpan($span);
        }

        $socket = new BridgeConnectionSocket($address, $port, $options);
        $response = '';
        $error = false;

        try {
            $socket->send();
            if ($socket->wantResponse) {
                $response = $socket->read();
                $error = $socket->error;
            }
        } catch (\Throwable $e) {
            if ($socket->wantResponse) {
                $response = $e->getMessage();
                $error = true;
            }
        }

        $socket->disconnect();

        if ($span !== null) {
            $span->finish();
            \Sentry\SentrySdk::getCurrentHub()->setSpan($parentSpan);
        }

        return new BridgeConnectionResponse($response, $error, $socket->cacheHit);
    }

    public function send(bool $wantResponse = true): BridgeConnectionResponse|Collection
    {
        $options = (object) [
            'message' => $this->message,
            'timeout' => $this->timeout,
            'force' => $this->force,
            'cacheFor' => $this->cacheFor,
            'wantResponse' => $wantResponse,
        ];

        $responses = collect();
        foreach ($this->targets as $target) {
            $responses->add($this->handler($target->address, $target->port, $options));
        }

        return count($responses) === 1 ? $responses->first() : $responses;
    }

    public function sendAndForget()
    {
        $this->send(false);
    }
}
