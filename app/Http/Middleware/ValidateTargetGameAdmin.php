<?php

namespace App\Http\Middleware;

use App\Services\CommonRequest;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateTargetGameAdmin
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
        if ($this->commonRequest->targetingGameAdmin()) {
            $gameAdmin = $this->commonRequest->targetGameAdmin();
            if (! $gameAdmin) {
                throw new AuthenticationException('Game admin not found.');
            }

            $serverId = $this->commonRequest->targetServerId();
            if ($serverId) {
                if (! $this->commonRequest->targetServer()) {
                    return abort(422, 'Invalid server ID.');
                }

                if (! $gameAdmin->hasAccessToServer($serverId)) {
                    throw new AuthenticationException('Game admin does not have access to this server.');
                }
            }
        }

        return $next($request);
    }
}
