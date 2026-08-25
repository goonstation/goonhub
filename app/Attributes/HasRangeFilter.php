<?php

namespace App\Attributes;

use Attribute;
use Dedoc\Scramble\Attributes\QueryParameter;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_ALL)]
class HasRangeFilter extends QueryParameter
{
    public function __construct(...$args)
    {
        $name = isset($args['name']) ? 'filters.'.$args['name'] : 'filters.range';
        unset($args['name']);
        $args = [
            'name' => $name,
            'type' => 'string',
            'description' => 'A value, comparison, or range',
            'example' => '1 or >= 1 or 1-10',
            'infer' => false,
            ...$args,
        ];
        parent::__construct(...$args);
    }
}
