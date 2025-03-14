<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Sentry\State\Scope;

class Sentry
{
    public function handle(Request $request, Closure $next, $type)
    {
        \Sentry\configureScope(function (Scope $scope) use ($type): void {
            $scope->setTag('route_type', $type);
        });

        return $next($request);
    }
}
