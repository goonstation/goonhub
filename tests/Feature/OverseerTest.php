<?php

namespace Tests\Feature;

use App\Services\Overseer;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use RuntimeException;
use Tests\TestCase;

class OverseerTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.overseer.token' => str_repeat('test', 8)]);
        Http::preventStrayRequests();
        Sleep::fake();
        $this->directory = sys_get_temp_dir().'/overseer-test-'.bin2hex(random_bytes(6));
        mkdir($this->directory, 0700);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory.'/*') ?: [] as $file) {
            unlink($file);
        }
        rmdir($this->directory);
        parent::tearDown();
    }

    public function test_authenticates_status_build_checks_and_maintenance(): void
    {
        Http::fake(fn () => Http::response(['result' => true]));
        $client = new Overseer;
        $this->assertSame(['result' => true], $client->status('https://overseer.test/', 'main1'));
        $client->checkBuild('https://overseer.test', ['server' => 'main1', 'buildstamp' => 123, 'byond' => '516.1666', 'rustg' => '3.4.0']);
        $client->maintenance('https://overseer.test');
        Http::assertSentCount(3);
        Http::assertSent(fn (Request $request) => $request->method() === 'GET' && $request->url() === 'https://overseer.test/status?server=main1');
        Http::assertSent(fn (Request $request) => str_contains($request->url(), '/build/check?server=main1&buildstamp=123&byond=516.1666&rustg=3.4.0'));
        Http::assertSent(fn (Request $request) => $request->url() === 'https://overseer.test/maintenance');
        foreach (Http::recorded() as [$request]) {
            $this->assertTrue($request->hasHeader('Authorization', 'Bearer '.str_repeat('test', 8)));
        }
    }

    public function test_restart_is_post_and_operation_is_polled_by_id(): void
    {
        Http::fake([
            '*/restart*' => Http::response(['id' => 'operation-1', 'server' => 'main1', 'state' => 'queued', 'statusUrl' => '/operations/operation-1'], 202),
            '*/operations/*' => Http::response(['id' => 'operation-1', 'server' => 'main1', 'state' => 'succeeded']),
        ]);
        $client = new Overseer;
        $operation = $client->restart('https://overseer.test', 'main1');
        $this->assertSame('queued', $operation['state']);
        $this->assertSame('succeeded', $client->operation('https://overseer.test', $operation['id'])['state']);
        Http::assertSent(fn (Request $request) => $request->method() === 'POST' && $request->url() === 'https://overseer.test/restart?server=main1');
        Http::assertSent(fn (Request $request) => $request->method() === 'GET' && $request->url() === 'https://overseer.test/operations/operation-1');
    }

    public function test_fallback_upload_is_authenticated_raw_binary_and_rewinds_after_drain(): void
    {
        $contents = "SQLite format 3\0\0binary\xff";
        file_put_contents($this->directory.'/main1.sqlite', $contents);
        $bodies = [];
        Http::fake(function (Request $request) use (&$bodies) {
            $this->assertSame('https://overseer.test/fallback/upload?server=main1', $request->url());
            $this->assertSame('POST', $request->method());
            $this->assertTrue($request->hasHeader('Authorization', 'Bearer '.str_repeat('test', 8)));
            $this->assertTrue($request->hasHeader('Content-Type', 'application/octet-stream'));
            $this->assertFalse($request->isMultipart());
            $bodies[] = $request->body();

            return count($bodies) === 1 ? Http::response(['message' => 'Draining'], 503, ['Retry-After' => '0']) : Http::response(['message' => 'Success'], 200);
        });
        (new Overseer)->uploadFallback('https://overseer.test', 'main1', $this->directory.'/main1.sqlite');
        $this->assertSame([$contents, $contents], $bodies);
    }

    public function test_build_upload_streams_correct_fields_and_declared_archive_types(): void
    {
        file_put_contents($this->directory.'/build.tar.gz', 'build bytes');
        file_put_contents($this->directory.'/runtime.zip', 'runtime bytes');
        Http::fake(function (Request $request) {
            $this->assertTrue($request->hasFile('build', null, 'build.tar.gz'));
            $this->assertTrue($request->hasFile('rustg', null, 'runtime.zip'));
            $parts = $request->data();
            $this->assertSame('application/gzip', $parts[0]['headers']['Content-Type']);
            $this->assertSame('application/zip', $parts[1]['headers']['Content-Type']);
            $this->assertTrue($request->hasHeader('Authorization', 'Bearer '.str_repeat('test', 8)));

            return Http::response(['message' => 'Success'], 200);
        });
        (new Overseer)->uploadBuild('https://overseer.test', 'main1', ['build' => $this->directory.'/build.tar.gz', 'rustg' => $this->directory.'/runtime.zip']);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://overseer.test/build/upload?server=main1');
    }

    public function test_missing_token_sends_nothing(): void
    {
        config(['services.overseer.token' => null]);
        Http::fake();
        try {
            (new Overseer)->status('https://overseer.test');
            $this->fail('Missing credentials must fail before sending');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('token', $exception->getMessage());
            Http::assertNothingSent();
        }
    }

    public function test_rejected_restart_is_not_retried_for_non_drain_errors(): void
    {
        Http::fake(fn () => Http::response(['message' => 'Busy'], 409));
        try {
            (new Overseer)->restart('https://overseer.test', 'main1');
            $this->fail('Rejected restart must fail');
        } catch (RuntimeException) {
            Http::assertSentCount(1);
        }
    }

    public function test_drain_retries_are_bounded_and_do_not_ignore_long_retry_after(): void
    {
        Http::fake(fn () => Http::response([], 503, ['Retry-After' => '0']));
        try {
            (new Overseer)->status('https://overseer.test');
            $this->fail('Exhausted retries must fail');
        } catch (RuntimeException) {
            Http::assertSentCount(3);
        }
    }

    public function test_long_retry_after_does_not_retry_early(): void
    {
        Http::fake(fn () => Http::response([], 503, ['Retry-After' => '300']));
        try {
            (new Overseer)->status('https://overseer.test');
            $this->fail('A long drain must be left to a later caller');
        } catch (RuntimeException) {
            Http::assertSentCount(1);
        }
    }

    public function test_unknown_container_status_allows_missing_inspection_fields(): void
    {
        $resource = new \App\Http\Resources\OverseerStatusResource(['status' => 'unknown', 'error' => 'Inspection failed']);
        $this->assertSame(['status' => 'unknown', 'health' => null, 'startedAt' => null, 'restarting' => false], $resource->resolve());
    }
}
