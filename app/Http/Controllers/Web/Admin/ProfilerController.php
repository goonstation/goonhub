<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class ProfilerController extends Controller
{
    private const LOGS_HOST = 'logs.goonhub.com';

    public function index()
    {
        return Inertia::render('Admin/Profiler/Index');
    }

    /**
     * Fetch a world.Profile() JSON dump from logs.goonhub.com.
     */
    public function fetch(Request $request)
    {
        $data = $request->validate([
            'url' => [
                'required',
                'url:https',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $parts = parse_url($value);
                    if (
                        ($parts['host'] ?? null) !== self::LOGS_HOST
                        || isset($parts['port'])
                        || ! preg_match('#^/[\w-]+/logs/profiling/[\w.-]+\.json$#', $parts['path'] ?? '')
                    ) {
                        $fail('The :attribute must be a profiler JSON file on '.self::LOGS_HOST.'.');
                    }
                },
            ],
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            $response = Http::withBasicAuth($data['username'], $data['password'])
                ->timeout(30)
                ->get($data['url']);
        } catch (ConnectionException) {
            abort(502, 'Could not reach '.self::LOGS_HOST.'.');
        }

        if ($response->unauthorized()) {
            abort(422, 'The '.self::LOGS_HOST.' username or password is incorrect.');
        }
        if ($response->notFound()) {
            abort(404, 'That profiler file does not exist.');
        }
        if (! $response->successful()) {
            abort(502, self::LOGS_HOST." responded with status {$response->status()}.");
        }

        return response($response->body(), 200, ['Content-Type' => 'application/json']);
    }
}
