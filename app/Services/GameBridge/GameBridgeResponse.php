<?php

namespace App\Services\GameBridge;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

class GameBridgeResponse implements Arrayable, Jsonable, JsonSerializable
{
    public string|float $message = '';

    public bool $error = false;

    public bool $cached = false;

    public ?string $serverId = null;

    public array $parsedData = [];

    public function __construct(string|float $message, bool $error = false, bool $cached = false, ?string $serverId = null)
    {
        $this->message = is_numeric($message) ? (float) $message : $message;
        $this->error = $error;
        $this->cached = $cached;
        $this->serverId = $serverId;

        // Auto-parse common response formats
        $this->parseResponse();
    }

    /**
     * Check if the response was successful
     */
    public function successful(): bool
    {
        return ! $this->error;
    }

    /**
     * Check if the response failed
     */
    public function failed(): bool
    {
        return $this->error;
    }

    /**
     * Get the raw message
     */
    public function getMessage(): string|float
    {
        return $this->message;
    }

    /**
     * Get the parsed data
     */
    public function getData(): array
    {
        return $this->parsedData;
    }

    /**
     * Get a specific data field
     */
    public function get(string $key, $default = null)
    {
        return $this->parsedData[$key] ?? $default;
    }

    /**
     * Check if a data field exists
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->parsedData);
    }

    /**
     * Get all data fields
     */
    public function all(): array
    {
        return $this->parsedData;
    }

    /**
     * Check if the response was cached
     */
    public function wasCached(): bool
    {
        return $this->cached;
    }

    /**
     * Get the server ID this response came from
     */
    public function getServerId(): ?string
    {
        return $this->serverId;
    }

    /**
     * Set the server ID
     */
    public function setServerId(string $serverId): self
    {
        $this->serverId = $serverId;

        return $this;
    }

    /**
     * Parse the response into structured data
     */
    private function parseResponse(): void
    {
        if ($this->error || ! is_string($this->message)) {
            return;
        }

        // Try to parse as query string format (common for game server responses)
        if (str_contains($this->message, '=')) {
            parse_str($this->message, $parsed);
            $this->parsedData = $parsed;

            return;
        }

        // Try to parse as JSON
        $jsonDecoded = json_decode($this->message, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $this->parsedData = $jsonDecoded;

            return;
        }

        // For other formats, store as raw data
        $this->parsedData = ['raw' => $this->message];
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'error' => $this->error,
            'cached' => $this->cached,
            'server_id' => $this->serverId,
            'data' => $this->parsedData,
            'successful' => $this->successful(),
        ];
    }

    /**
     * Convert to JSON
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * JSON serialize
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * String representation
     */
    public function __toString(): string
    {
        return (string) $this->message;
    }

    /**
     * Get response as a numeric value if possible
     */
    public function asNumber(): int
    {
        if (is_numeric($this->message)) {
            return (int) $this->message;
        }

        return 0;
    }

    /**
     * Throw an exception if the response failed
     */
    public function throw(): self
    {
        if ($this->failed()) {
            throw new GameBridgeException("GameBridge request failed: {$this->message}", $this);
        }

        return $this;
    }

    /**
     * Execute a callback if the response was successful
     */
    public function onSuccess(callable $callback): self
    {
        if ($this->successful()) {
            $callback($this);
        }

        return $this;
    }

    /**
     * Execute a callback if the response failed
     */
    public function onFailure(callable $callback): self
    {
        if ($this->failed()) {
            $callback($this);
        }

        return $this;
    }
}
