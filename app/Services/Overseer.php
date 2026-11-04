<?php

namespace App\Services;

use GuzzleHttp\Psr7\Utils;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use RuntimeException;

class Overseer
{
    public function status(string $url, ?string $server = null): array
    {
        return $this->json($this->request(fn ($request) => $request->get($this->url($url, '/status'), $server === null ? [] : ['server' => $server])));
    }

    public function restart(string $url, string $server): array
    {
        return $this->json($this->request(fn ($request) => $request->withQueryParameters(['server' => $server])->post($this->url($url, '/restart')), 202));
    }

    public function operation(string $url, string $id): array
    {
        return $this->json($this->request(fn ($request) => $request->get($this->url($url, '/operations/'.rawurlencode($id)))));
    }

    public function maintenance(string $url): array
    {
        return $this->json($this->request(fn ($request) => $request->get($this->url($url, '/maintenance'))));
    }

    /** @param array{server: string, buildstamp: int, byond: string, rustg: string} $parameters */
    public function checkBuild(string $url, array $parameters): array
    {
        return $this->json($this->request(fn ($request) => $request->get($this->url($url, '/build/check'), $parameters)));
    }

    public function uploadFallback(string $url, string $server, string $path): void
    {
        $this->request(function (PendingRequest $request) use ($url, $server, $path) {
            $stream = $this->open($path);
            try {
                return $request->withBody(Utils::streamFor($stream), 'application/octet-stream')
                    ->withQueryParameters(['server' => $server])
                    ->post($this->url($url, '/fallback/upload'));
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }, 200, (int) config('services.overseer.fallback_timeout', 30));
    }

    /** @param array<string, string> $files Field name => local archive path. */
    public function uploadBuild(string $url, string $server, array $files): void
    {
        if ($files === []) {
            throw new RuntimeException('No Overseer build artifacts supplied.');
        }
        $this->request(function (PendingRequest $request) use ($url, $server, $files) {
            $streams = [];
            try {
                foreach ($files as $field => $path) {
                    if (! in_array($field, ['build', 'byond', 'rustg'], true)) {
                        throw new RuntimeException('Unsupported Overseer artifact field.');
                    }
                    $type = str_ends_with($path, '.zip') ? 'application/zip' : (str_ends_with($path, '.tar.gz') ? 'application/gzip' : null);
                    if ($type === null) {
                        throw new RuntimeException('Unsupported Overseer archive type.');
                    }
                    $streams[] = $stream = $this->open($path);
                    $request->attach($field, $stream, basename($path), ['Content-Type' => $type]);
                }

                return $request->withQueryParameters(['server' => $server])->post($this->url($url, '/build/upload'));
            } finally {
                foreach ($streams as $stream) {
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                }
            }
        }, 200, (int) config('services.overseer.upload_timeout', 330));
    }

    /** @param callable(PendingRequest): Response $send */
    private function request(callable $send, int $expectedStatus = 200, ?int $timeout = null): Response
    {
        $token = config('services.overseer.token');
        if (! is_string($token) || trim($token) === '') {
            throw new RuntimeException('Overseer API token is not configured.');
        }
        for ($attempt = 0; $attempt < 3; $attempt++) {
            // Rebuild the request and reopen upload streams on each rejected attempt.
            $response = $send(Http::withToken($token)->acceptJson()->withoutRedirecting()
                ->connectTimeout((int) config('services.overseer.connect_timeout', 10))
                ->timeout($timeout ?? (int) config('services.overseer.timeout', 30)));
            if ($response->status() === $expectedStatus) {
                return $response;
            }
            if ($response->status() !== 503 || $attempt === 2) {
                throw new RuntimeException('Overseer request failed (HTTP '.$response->status().').');
            }
            $header = $response->header('Retry-After');
            $delay = $header === '' ? 1 : (ctype_digit($header) ? (int) $header : max(0, (strtotime($header) ?: time()) - time()));
            if ($delay > 10) {
                throw new RuntimeException('Overseer is unavailable; retry after '.$delay.' seconds.');
            }
            Sleep::for($delay)->seconds();
        }
        throw new RuntimeException('Overseer request failed.');
    }

    private function url(string $url, string $path): string
    {
        $url = rtrim(trim($url), '/');
        if ($url === '') {
            throw new RuntimeException('Overseer URL is not configured.');
        }

        return $url.$path;
    }

    private function json(Response $response): array
    {
        $data = $response->json();
        if (! is_array($data)) {
            throw new RuntimeException('Invalid Overseer JSON.');
        }

        return $data;
    }

    /** @return resource */
    private function open(string $path)
    {
        $stream = @fopen($path, 'rb');
        if ($stream === false) {
            throw new RuntimeException('Cannot open Overseer upload file.');
        }

        return $stream;
    }
}
