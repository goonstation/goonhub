<?php

namespace App\Libraries\GameBridge;

use Illuminate\Contracts\Cache\Lock;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Socket;

class BridgeConnectionSocket
{
    private string $ip = '';

    private int $port = 0;

    private int $timeout = 5;

    private string $message = '';

    private bool $force = false;

    private Socket|bool $socket = false;

    private ?Lock $lock = null;

    private bool $blocked = false;

    private string $lockKey = '';

    private string $cacheKey = '';

    private int $cacheFor = 30;

    public bool $wantResponse = false;

    public bool $cacheHit = false;

    public bool $error = false;

    public function __construct(string $ip, int $port, object $options)
    {
        $this->ip = $ip;
        $this->port = $port;

        $this->timeout = $options->timeout ?: 5;
        $this->force = $options->force ?: false;
        $this->cacheFor = $options->cacheFor ?: 60;
        $this->wantResponse = $options->wantResponse ?: false;

        $msgHash = md5($options->message);
        $this->lockKey = "GameBridge-lock-{$this->ip}-{$this->port}-$msgHash";
        $this->cacheKey = "GameBridge-{$this->ip}-{$this->port}-$msgHash";
        $this->message = $options->message;
    }

    private function error()
    {
        $code = socket_last_error();
        $msg = socket_strerror($code);
        $this->disconnect();
        throw new RuntimeException($msg, $code);
    }

    private function create()
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (! ($this->socket instanceof Socket)) {
            return $this->error();
        }
    }

    private function connect()
    {
        $this->create();
        $timeoutOption = ['sec' => $this->timeout, 'usec' => 0];
        socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, $timeoutOption);
        socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, $timeoutOption);
        socket_set_nonblock($this->socket);

        $attempts = 0;
        $msTimeout = $this->timeout * 1000;
        $connected = false;
        while (! $connected && $attempts < $msTimeout) {
            $connected = @socket_connect($this->socket, $this->ip, $this->port);
            $error = socket_last_error();
            if ($error && $error != SOCKET_EINPROGRESS && $error != SOCKET_EALREADY) {
                return $this->error();
            }
            usleep(1000); // 1 millisecond
            $attempts++;
        }

        if (! $connected) {
            return $this->error();
        }
        socket_set_block($this->socket);
    }

    public function disconnect()
    {
        if ($this->socket) {
            try {
                socket_close($this->socket);
            } catch (\Throwable) {
                // possible we already closed this socket
            }
        }
        if ($this->lock) {
            $this->lock->forceRelease();
        }
    }

    private function createPacket(): string
    {
        return pack('C2nC5a'.strlen($this->message) + 1, ...[
            0x00, 0x83,
            strlen($this->message) + 6,
            0x00, 0x00, 0x00, 0x00, 0x00,
            $this->message,
        ]);
    }

    private function readPacket(string $packet): string|float
    {
        $types = [
            'float' => pack('c', 0x2A),
            'text' => pack('c', 0x06),
        ];

        $rsize = unpack('n', substr($packet, 1, 2))[1] - 1;
        $type = substr($packet, 3, 1);

        $data = '';
        if ($type === $types['float'] && $rsize === 4) {
            $bytes = substr($packet, 4, 4);
            if ($bytes) {
                $data = unpack('g', $bytes)[1] - 1;
            }
        } elseif ($type === $types['text']) {
            $data = unpack('a*', substr($packet, 4, $rsize - 1));
        }

        return is_array($data) ? $data[1] : $data;
    }

    private function getCache()
    {
        $cacheItem = Cache::get($this->cacheKey);
        if (array_key_exists('error', $cacheItem)) {
            $this->error = true;
        }

        return $cacheItem['response'];
    }

    public function read(): string|float
    {
        $cacheExists = Cache::has($this->cacheKey);
        if (! $this->blocked && $this->force && $cacheExists) {
            Cache::delete($this->cacheKey);
        }

        $elapsed = 0;
        while ($this->blocked && ! $cacheExists) {
            usleep(100000); // 100 milliseconds
            $elapsed += 100000;
            $cacheExists = Cache::has($this->cacheKey);
            if ($cacheExists) {
                $this->cacheHit = true;

                return $this->getCache();
            }
            if ($elapsed >= $this->timeout * 1000000) {
                throw new RuntimeException('Timeout while blocked');
            }
        }

        if ($cacheExists && ! $this->force) {
            $this->cacheHit = true;

            return $this->getCache();
        }

        $out = '';
        while ($out = socket_read($this->socket, 5120)) {
            if ($out = trim($out)) {
                break;
            }
        }

        if ($out === false) {
            $response = socket_strerror(socket_last_error());
            Cache::set($this->cacheKey, ['response' => $response, 'error' => true], $this->cacheFor);

            return $this->error();
        }

        $response = $this->readPacket($out);
        Cache::set($this->cacheKey, ['response' => $response], $this->cacheFor);

        return $response;
    }

    public function send()
    {
        $this->lock = Cache::lock($this->lockKey, 30);
        if (! $this->lock->get()) {
            // Another process is currently running this exact query
            $this->blocked = true;

            return $this;
        }

        if (Cache::has($this->cacheKey) && $this->wantResponse && ! $this->force) {
            // We recently ran this exact query, and it returns a response we read later
            return $this;
        }

        $this->connect();
        $packet = $this->createPacket();
        // dump("Sending {$this->message} to {$this->ip}:{$this->port} with {$this->timeout}s timeout");

        $packetLength = strlen($packet);
        $sent = 0;
        while ($sent < $packetLength) {
            $sent = socket_write($this->socket, $packet, $packetLength);
            if ($sent === false) {
                return $this->error();
            }
            $packet = substr($packet, $sent);
            $packetLength -= $sent;
        }

        return $this;
    }
}
