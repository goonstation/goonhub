<?php

namespace App\Libraries;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class OpenGraphImage
{
    private $chromeUserData = '/tmp/chrome-user-data';

    private $storagePath = '';

    private $pathSuffix = 'img/og';

    public function __construct()
    {
        if (! File::exists($this->chromeUserData)) {
            File::makeDirectory($this->chromeUserData, 0755, true);
        }

        $this->storagePath = storage_path('app/public/'.$this->pathSuffix);
        if (! File::exists($this->storagePath)) {
            File::makeDirectory($this->storagePath, 0755, true);
        }
    }

    public function getCacheLength(): int
    {
        return 1 * 24 * 60 * 60; // 1 day
    }

    protected function makeImage(string $type, $data, string $path): bool
    {
        $response = null;
        try {
            /** @var \Illuminate\Http\Client\Response|\Psr\Http\Message\MessageInterface */
            $response = Http::withHeaders([
                'Cache-Control' => 'no-cache',
                'Content-Type' => 'application/json',
            ])->post(
                config('goonhub.browserless_host').
                ':'.config('goonhub.browserless_port').
                '/screenshot?'.
                'token='.config('goonhub.browserless_token'),
                [
                    'html' => view("open-graph.$type", ['data' => $data])->render(),
                    'viewport' => [
                        'width' => 1200,
                        'height' => 630,
                    ],
                    'options' => [
                        'type' => 'png',
                        'fullPage' => true,
                    ],
                ]);
        } catch (ConnectionException) {
            return false;
        }

        $body = $response->getBody();
        if ($response->failed() || ! $body->getSize()) {
            return false;
        }

        File::put($path, $body);

        return true;
    }

    protected function getFullPath(string $type, string|int $key): string
    {
        return $this->storagePath.'/'.$type.'/'.$key.'.png';
    }

    protected function getFileAge(string $path): int
    {
        return time() - File::lastModified($path);
    }

    protected function getEtag(string $path): string
    {
        return md5((string) File::lastModified($path));
    }

    protected function isFileExpired(string $path, ?int $cacheLength = null): bool
    {
        return $this->getFileAge($path) > ($cacheLength ?: $this->getCacheLength());
    }

    public function getFile(string $type, string|int $key, ?int $cacheLength = null): array|bool
    {
        $fullPath = $this->getFullPath($type, $key);
        if (! File::exists($fullPath)) {
            return false;
        }
        if ($this->isFileExpired($fullPath, $cacheLength)) {
            return false;
        }

        return [
            'age' => $this->getFileAge($fullPath),
            'etag' => $this->getEtag($fullPath),
            'file' => file_get_contents($fullPath),
        ];
    }

    public function getImage(string $type, string|int $key, $data, ?int $cacheLength = null): array|bool
    {
        $path = $this->storagePath.'/'.$type;
        $fullPath = $this->getFullPath($type, $key);

        $madeImage = false;
        if (! File::exists($fullPath) || $this->isFileExpired($fullPath, $cacheLength)) {
            if (! File::exists($path)) {
                File::makeDirectory($path, 0755, true);
            }
            $madeImage = $this->makeImage($type, $data, $fullPath);
        }

        if (! $madeImage) {
            return false;
        }

        return [
            'age' => $this->getFileAge($fullPath),
            'etag' => $this->getEtag($fullPath),
            'file' => file_get_contents($fullPath),
        ];
    }
}
