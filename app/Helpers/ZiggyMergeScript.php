<?php

namespace App\Helpers;

use Stringable;
use Tighten\Ziggy\Ziggy;

class ZiggyMergeScript implements Stringable
{
    public function __construct(
        protected Ziggy $ziggy,
        protected string $nonce = '',
    ) {}

    public function __toString(): string
    {
        $routes = $this->ziggy->toArray()['routes'];
        foreach ($routes as $name => &$route) {
            $route = array_merge($route, GenerateRouteAccess::getRoutePermissions($name));
        }
        $routes = json_encode($routes);

        return <<<HTML
        <script type="text/javascript"{$this->nonce}>Object.assign(Ziggy.routes,{$routes});</script>
        HTML;
    }
}
