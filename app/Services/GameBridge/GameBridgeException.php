<?php

namespace App\Services\GameBridge;

use Exception;

class GameBridgeException extends Exception
{
    private ?GameBridgeResponse $response;

    public function __construct(string $message, ?GameBridgeResponse $response = null, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
    }

    /**
     * Get the response that caused this exception
     */
    public function getResponse(): ?GameBridgeResponse
    {
        return $this->response;
    }

    /**
     * Check if this exception has a response
     */
    public function hasResponse(): bool
    {
        return $this->response !== null;
    }

    /**
     * Get the server ID from the response if available
     */
    public function getServerId(): ?string
    {
        return $this->response?->getServerId();
    }
}
