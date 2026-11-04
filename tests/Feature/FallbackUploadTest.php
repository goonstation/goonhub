<?php

namespace Tests\Feature;

use App\Services\FallbackUpload;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class FallbackUploadTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.overseer.token' => str_repeat('test', 8)]);
        $this->directory = sys_get_temp_dir().'/fallback-upload-'.bin2hex(random_bytes(6));
        mkdir($this->directory, 0700);
        file_put_contents($this->directory.'/main1.sqlite', 'snapshot one');
        file_put_contents($this->directory.'/main3.sqlite', 'snapshot three');
        file_put_contents($this->directory.'/inactive.sqlite', 'stale');
        file_put_contents($this->directory.'/main1.sqlite.lock', '');
        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory.'/*') ?: [] as $file) {
            unlink($file);
        }
        rmdir($this->directory);
        parent::tearDown();
    }

    public function test_uploads_each_server_file_separately_with_its_server_parameter(): void
    {
        $payloads = [];
        Http::fake(function (Request $request) use (&$payloads) {
            $this->assertSame('POST', $request->method());
            parse_str(parse_url($request->url(), PHP_URL_QUERY), $parameters);
            $server = $parameters['server'];
            $this->assertFalse($request->isMultipart());
            $this->assertTrue($request->hasHeader('Authorization', 'Bearer '.str_repeat('test', 8)));
            $this->assertTrue($request->hasHeader('Content-Type', 'application/octet-stream'));
            $contents = $request->body();
            $payloads[$server] = $contents;

            return Http::response('', 200);
        });
        (app(FallbackUpload::class))->upload($this->directory, [
            ['server_id' => 'main1', 'overseer' => 'https://overseer.test/'],
            ['server_id' => 'main3', 'overseer' => 'https://overseer.test'],
        ]);
        Http::assertSentCount(2);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://overseer.test/fallback/upload?server=main1');
        Http::assertSent(fn (Request $request) => $request->url() === 'https://overseer.test/fallback/upload?server=main3');
        $this->assertSame(['main1' => 'snapshot one', 'main3' => 'snapshot three'], $payloads);
        $this->assertCount(4, glob($this->directory.'/*'));
    }

    public function test_failed_upload_attempts_remaining_servers_and_preserves_files(): void
    {
        Http::fake(fn (Request $request) => Http::response('', str_contains($request->url(), 'server=main1') ? 500 : 200));
        try {
            (app(FallbackUpload::class))->upload($this->directory, [
                ['server_id' => 'main1', 'overseer' => 'https://overseer.test'],
                ['server_id' => 'main3', 'overseer' => 'https://overseer.test'],
            ]);
            $this->fail('Upload failure must fail the job');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('1', $exception->getMessage());
            Http::assertSentCount(2);
            $this->assertSame('snapshot one', file_get_contents($this->directory.'/main1.sqlite'));
            $this->assertSame('snapshot three', file_get_contents($this->directory.'/main3.sqlite'));
        }
    }

    public function test_missing_file_fails_but_remaining_servers_are_uploaded(): void
    {
        Http::fake();
        try {
            (app(FallbackUpload::class))->upload($this->directory, [
                ['server_id' => 'missing', 'overseer' => 'https://overseer.test'],
                ['server_id' => 'main3', 'overseer' => 'https://overseer.test'],
            ]);
            $this->fail('Missing upload source must fail the job');
        } catch (RuntimeException) {
            Http::assertSentCount(1);
            Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '?server=main3'));
        }
    }

    public function test_empty_servers_or_unconfigured_overseers_do_not_upload(): void
    {
        Http::fake();
        (app(FallbackUpload::class))->upload($this->directory, []);
        (app(FallbackUpload::class))->upload($this->directory, [
            ['server_id' => 'main1', 'overseer' => null],
            ['server_id' => 'main3', 'overseer' => ''],
        ]);
        Http::assertNothingSent();
    }
}
