<?php

namespace App\Helpers;

use App\Exceptions\ByondOutageException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class QueryByond
{
    // How long to wait for a response from Byond, in seconds
    const REQUEST_TIMEOUT = 5;

    // How many connection errors to tolerate before preventing further requests
    const ERROR_LIMIT = 3;

    // How long to block requests after reaching the error limit, in minutes
    const ERROR_BLOCK_TIME = 20;

    // The cache key for the connection error count
    const ERROR_CACHE_KEY = 'byond_connection_errors';

    public static function query(string $url, ?PendingRequest $request = null)
    {
        if (Cache::get(self::ERROR_CACHE_KEY) >= self::ERROR_LIMIT) {
            throw new ByondOutageException;
        }

        $response = null;
        try {
            if (! $request) {
                $request = Http::createPendingRequest();
            }
            $response = $request->timeout(self::REQUEST_TIMEOUT)->throw()->get($url);
            Cache::delete(self::ERROR_CACHE_KEY);
        } catch (ConnectionException $e) {
            if (Cache::has(self::ERROR_CACHE_KEY)) {
                Cache::increment(self::ERROR_CACHE_KEY);
            } else {
                Cache::put(self::ERROR_CACHE_KEY, 1, now()->addMinutes(self::ERROR_BLOCK_TIME));
            }

            throw $e;
        }

        return $response;
    }
}
