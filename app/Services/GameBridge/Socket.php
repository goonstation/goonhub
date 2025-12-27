<?php

namespace App\Services\GameBridge;

use Illuminate\Contracts\Cache\Lock;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Socket as SocketResource;

class Socket
{
    private string $ip = '';

    private int $port = 0;

    private int $timeout = 5;

    private string $message = '';

    private bool $force = false;

    private SocketResource|bool $socket = false;

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
        $this->lockKey = "GameBridge-lock-{$this->ip}-{$this->port}-{$msgHash}";
        $this->cacheKey = "GameBridge-{$this->ip}-{$this->port}-{$msgHash}";
        $this->message = $options->message;
    }

    public function isCached(): bool
    {
        $lock = Cache::lock($this->lockKey, 30);
        if ($lock->owner()) {
            return true;
        }

        if (Cache::has($this->cacheKey) && $this->wantResponse && ! $this->force) {
            // We recently ran this exact query, and it returns a response we read later
            return true;
        }

        return false;
    }

    /**
     * Handle socket errors
     */
    private function error(): void
    {
        $code = socket_last_error();
        $msg = socket_strerror($code);
        $this->disconnect();
        throw new RuntimeException($msg, $code);
    }

    /**
     * Create socket resource
     */
    private function create(): void
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (! ($this->socket instanceof SocketResource)) {
            $this->error();
        }
    }

    /**
     * Connect to the socket
     */
    private function connect(): void
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
                $this->error();
            }

            usleep(1000); // 1 millisecond
            $attempts++;
        }

        if (! $connected) {
            $this->error();
        }

        socket_set_block($this->socket);
    }

    /**
     * Disconnect from the socket
     */
    public function disconnect(): void
    {
        if ($this->socket) {
            try {
                socket_close($this->socket);
            } catch (\Throwable) {
                // Socket may already be closed
            }
        }

        if ($this->lock) {
            $this->lock->forceRelease();
        }
    }

    /**
     * Create packet for sending
     */
    private function createPacket(): string
    {
        return pack('C2nC5a'.strlen($this->message) + 1, ...[
            0x00, 0x83,
            strlen($this->message) + 6,
            0x00, 0x00, 0x00, 0x00, 0x00,
            $this->message,
        ]);
    }

    /**
     * Read and parse packet response
     */
    private function readPacket(string $packet): string|float
    {
        if (strlen($packet) < 4) {
            return '';
        }

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

    /**
     * Get cached response
     */
    private function getCache(): string|float
    {
        $cacheItem = Cache::get($this->cacheKey);
        if (array_key_exists('error', $cacheItem)) {
            $this->error = true;
        }

        return $cacheItem['response'];
    }

    /**
     * Read response from socket
     */
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

        return $this->readWithRetry();
    }

    /**
     * Read from socket with retry logic for interrupted system calls
     */
    private function readWithRetry(): string|float
    {
        $out = '';
        $elapsed = 0;
        $retryCount = 0;
        $maxRetries = 3;
        $baseDelay = 1000; // 1ms in microseconds

        while (true) {
            $out = @socket_read($this->socket, 5120);

            if ($out !== false) {
                // Successfully read data
                if ($out = trim($out)) {
                    break;
                }
                // Empty data, continue reading
                usleep(10000); // 10 milliseconds
                $elapsed += 10000;
            } else {
                // Read failed, check error code
                $errorCode = socket_last_error();

                if ($errorCode === SOCKET_EINTR) {
                    // Interrupted system call - this is recoverable
                    $retryCount++;

                    if ($retryCount > $maxRetries) {
                        Log::warning('GameBridge socket read: Maximum retries exceeded for EINTR', [
                            'ip' => $this->ip,
                            'port' => $this->port,
                            'retries' => $retryCount,
                            'elapsed_ms' => $elapsed / 1000,
                        ]);

                        throw new RuntimeException('Maximum retries exceeded for interrupted system call');
                    }

                    // Exponential backoff: 1ms, 2ms, 4ms
                    $delay = $baseDelay * (2 ** ($retryCount - 1));
                    usleep($delay);
                    $elapsed += $delay;

                    Log::debug('GameBridge socket read: Retrying after EINTR', [
                        'ip' => $this->ip,
                        'port' => $this->port,
                        'retry' => $retryCount,
                        'delay_us' => $delay,
                    ]);

                    // Clear the error and continue
                    socket_clear_error($this->socket);

                    continue;
                } elseif ($errorCode === SOCKET_EAGAIN) {
                    // Non-blocking socket would block (EAGAIN/EWOULDBLOCK) - this is expected, continue reading
                    usleep(10000); // 10 milliseconds
                    $elapsed += 10000;
                } else {
                    // Non-recoverable error
                    $response = socket_strerror($errorCode);

                    Log::error('GameBridge socket read: Non-recoverable error', [
                        'ip' => $this->ip,
                        'port' => $this->port,
                        'error_code' => $errorCode,
                        'error_message' => $response,
                        'elapsed_ms' => $elapsed / 1000,
                    ]);

                    Cache::set($this->cacheKey, ['response' => $response, 'error' => true], $this->cacheFor);
                    $this->error();
                }
            }

            // Check for overall timeout
            if ($elapsed >= $this->timeout * 1000000) {
                Log::warning('GameBridge socket read: Timeout exceeded', [
                    'ip' => $this->ip,
                    'port' => $this->port,
                    'timeout_seconds' => $this->timeout,
                    'elapsed_ms' => $elapsed / 1000,
                    'retries' => $retryCount,
                ]);

                throw new RuntimeException('Timeout while reading');
            }
        }

        $response = $this->readPacket($out);
        Cache::set($this->cacheKey, ['response' => $response], $this->cacheFor);

        return $response;
    }

    /**
     * Send data to socket
     */
    public function send(): self
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

        $packetLength = strlen($packet);
        $totalSent = 0;
        $retryCount = 0;
        $maxRetries = 3;

        while ($totalSent < $packetLength) {
            $sent = @socket_write($this->socket, $packet, $packetLength);

            if ($sent === false) {
                $errorCode = socket_last_error();

                if ($errorCode === SOCKET_EINTR) {
                    // Interrupted system call - retry with exponential backoff
                    $retryCount++;

                    if ($retryCount > $maxRetries) {
                        Log::warning('GameBridge socket write: Maximum retries exceeded for EINTR', [
                            'ip' => $this->ip,
                            'port' => $this->port,
                            'retries' => $retryCount,
                        ]);

                        $this->error();
                    }

                    $delay = 1000 * (2 ** ($retryCount - 1)); // 1ms, 2ms, 4ms
                    usleep($delay);

                    Log::debug('GameBridge socket write: Retrying after EINTR', [
                        'ip' => $this->ip,
                        'port' => $this->port,
                        'retry' => $retryCount,
                        'delay_us' => $delay,
                    ]);

                    socket_clear_error($this->socket);

                    continue;
                }

                // Non-recoverable error
                Log::error('GameBridge socket write: Non-recoverable error', [
                    'ip' => $this->ip,
                    'port' => $this->port,
                    'error_code' => $errorCode,
                    'error_message' => socket_strerror($errorCode),
                ]);

                $this->error();
            }

            // Successful write
            $retryCount = 0; // Reset retry count on successful write
            $totalSent += $sent;
            $packet = substr($packet, $sent);
            $packetLength -= $sent;
        }

        return $this;
    }
}
