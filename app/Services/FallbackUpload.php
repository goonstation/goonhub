<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class FallbackUpload
{
    public function __construct(private readonly Overseer $overseer) {}

    /** @param array<array{server_id: string, overseer: string|null}> $servers */
    public function upload(string $directory, array $servers): void
    {
        $failures = 0;
        foreach ($servers as $server) {
            $url = rtrim(trim($server['overseer'] ?? ''), '/');
            if ($url === '') {
                continue;
            }
            $serverId = $server['server_id'];
            try {
                $this->overseer->uploadFallback($url, $serverId, $directory.'/'.rawurlencode($serverId).'.sqlite');
            } catch (Throwable) {
                Log::error('Fallback file upload failed.', ['server_id' => $serverId]);
                $failures++;
            }
        }
        if ($failures > 0) {
            throw new RuntimeException('Fallback file upload failed for '.$failures.' game server(s).');
        }
    }
}
