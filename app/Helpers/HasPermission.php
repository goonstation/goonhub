<?php

namespace App\Helpers;

use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;

class HasPermission
{
    /**
     * Specify the ability and methods for the middleware.
     */
    public static function using(array|string|\BackedEnum $permission, ?string $guard = null, ?array $only = null, ?array $except = null)
    {
        return new Middleware(PermissionMiddleware::using($permission, $guard), $only, $except);
    }
}
