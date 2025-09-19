<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServerPerformanceMetricsResource;
use App\Http\Resources\ServerPerformanceResource;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

#[Group('Server Performance')]
class ServerPerformanceController extends Controller
{
    private function getPerformance(string $address)
    {
        if (! config('goonhub.server_performance.key')) {
            return null;
        }

        return Cache::remember(
            'server_performance_'.$address,
            60, // seconds
            function () use ($address) {
                $res = null;
                try {
                    $res = Http::withHeaders([
                        'Accept' => 'application/json',
                        'Authorization' => config('goonhub.server_performance.key'),
                        'User-Agent' => 'Goonhub',
                    ])
                        ->get('http://'.$address);
                } catch (ConnectionException $e) {
                    return null;
                }

                if ($res->status() === 401) {
                    return null;
                }

                $jsonRes = (string) $res->getBody();

                return json_decode($jsonRes, true);
            }
        );
    }

    /**
     * Index
     *
     * Fetch performance metrics for all available servers
     */
    public function index(Request $request)
    {
        $res = [];
        foreach (config('goonhub.server_performance.servers', []) as $name => $address) {
            $performanceData = $this->getPerformance($address);
            $res[] = new ServerPerformanceResource([
                'name' => $name,
                'metrics' => $performanceData ? new ServerPerformanceMetricsResource($performanceData) : null,
            ]);
        }

        return response()->json([
            /** @var ServerPerformanceResource[] */
            'data' => $res,
        ]);
    }
}
