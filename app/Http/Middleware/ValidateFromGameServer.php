<?php

namespace App\Http\Middleware;

use App\Services\CommonRequest;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Symfony\Component\HttpFoundation\Response;

class ValidateFromGameServer
{
    public function __construct(
        private readonly CommonRequest $commonRequest,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (EnsureFrontendRequestsAreStateful::fromFrontend($request)) {
            return $next($request);
        }

        $serverId = $this->commonRequest->fromServerId();

        if (! $serverId) {
            return abort(422, 'Missing server ID header.');
        }

        /** @var \App\Models\User */
        $user = $request->user();

        /** @var \App\Models\PersonalAccessToken */
        $token = $user->currentAccessToken();

        // TODO: clear cache when token servers change
        $tokenServers = Cache::rememberForever('token_servers_'.$token->id, function () use ($token) {
            return $token->allServers()->pluck('server_id')->toArray();
        });
        if (! in_array($serverId, $tokenServers)) {
            abort(403, 'Token has no access to this game server.');
        }

        return $next($request);
    }
}
