<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VpnCheckResource;
use App\Models\VpnCheck;
use App\Models\VpnWhitelist;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

#[Group('VPN Checks')]
class VpnChecksController extends Controller
{
    private $checkCacheTime = 30; // days

    /**
     * Check
     *
     * Check if a player is using a VPN
     */
    public function check(Request $request, string $ip)
    {
        $data = $this->validate($request, [
            'round_id' => 'nullable|integer',
            'ckey' => 'nullable|string',
        ]);

        // Skip checks if whitelisted
        if (! empty($data['ckey'])) {
            $whitelisted = VpnWhitelist::where('ckey', $data['ckey'])->exists();
            if ($whitelisted) {
                return [
                    'data' => [
                        'meta' => [
                            'cached' => false,
                            'whitelisted' => true,
                        ],
                    ],
                ];
            }
        }

        // Return cached response if available, and still valid
        $checked = VpnCheck::where('ip', $ip)->first();
        if ($checked && $checked->updated_at->diffInDays() <= $this->checkCacheTime) {
            $checked['meta'] = [
                'cached' => true,
                'whitelisted' => false,
            ];

            return new VpnCheckResource($checked);
        }

        $res = null;

        try {
            $res = Http::get(
                'https://ipqualityscore.com/api/json/ip/'
                    .config('goonhub.ipquality_pass')
                    .'/'.$ip,
                [
                    'allow_public_access_points' => 'true',
                    'fast' => 'true',
                    'lighter_penalties' => 'true',
                    'strictness' => 0,
                ]
            );
        } catch (ConnectionException $e) {
            return response()->json(['message' => 'Unable to query external VPN check service'], 400);
        }

        $jsonRes = (string) $res->getBody();
        $res = json_decode($jsonRes);

        if (is_null($res)) {
            return response()->json(['message' => 'Unable to query external VPN check service'], 400);
        }

        $vpnCheck = VpnCheck::updateOrCreate(
            ['ip' => $ip],
            [
                'round_id' => ! empty($data['round_id']) ? $data['round_id'] : null,
                'service' => 'ipqualityscore',
                'message' => ! $res->success ? $res->message : null,
                'response' => $jsonRes,
            ]
        );

        $vpnCheck['meta'] = [
            'cached' => false,
            'whitelisted' => false,
        ];

        return new VpnCheckResource($vpnCheck);
    }
}
