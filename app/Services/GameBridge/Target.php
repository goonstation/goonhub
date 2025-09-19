<?php

namespace App\Services\GameBridge;

class Target
{
    public string $serverId;

    public string $address;

    public int $port;

    public function __construct(string $serverId, string $address, int $port)
    {
        $this->serverId = $serverId;
        $this->address = $address;
        $this->port = $port;
    }

    /**
     * Create a Target from a GameServer model
     */
    public static function fromGameServer(\App\Models\GameServer $server): self
    {
        return new self(
            $server->server_id,
            $server->address,
            $server->port
        );
    }

    /**
     * Create a Target from an array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['server_id'] ?? $data['serverId'],
            $data['address'],
            $data['port']
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'serverId' => $this->serverId,
            'address' => $this->address,
            'port' => $this->port,
        ];
    }

    /**
     * Get server identifier for display
     */
    public function getServerId(): string
    {
        return $this->serverId;
    }

    /**
     * Get connection address
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * Get connection port
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * String representation
     */
    public function __toString(): string
    {
        return "{$this->serverId} ({$this->address}:{$this->port})";
    }
}
