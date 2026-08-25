<?php

namespace App\Helpers;

use Stringable;
use Tighten\Ziggy\Ziggy;

class ZiggyScript implements Stringable
{
    public function __construct(
        protected Ziggy $ziggy,
        protected string $function,
        protected string $nonce = '',
    ) {}

    public function __toString(): string
    {
        $json = $this->ziggy->jsonSerialize();
        foreach ($json['routes'] as $name => &$route) {
            $route = array_merge($route, GenerateRouteAccess::getRoutePermissions($name));
        }
        $json = json_encode($json);

        return <<<HTML
        <script type="text/javascript"{$this->nonce}>const Ziggy={$json};{$this->function}</script>
        HTML;
    }
}
