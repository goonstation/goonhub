<?php

namespace App\Attributes;

use Attribute;
use Dedoc\Scramble\Attributes\QueryParameter;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_ALL)]
class HasDateRangeFilter extends QueryParameter
{
    public function __construct(...$args)
    {
        $name = isset($args['name']) ? 'filters.'.$args['name'] : 'filters.created_at';
        unset($args['name']);
        $args = [
            'name' => $name,
            'type' => 'string',
            'description' => 'A date or date range',
            'example' => '2023/01/30 12:00:00 - 2023/02/01 12:00:00',
            'format' => 'date',
            'infer' => false,
            ...$args,
        ];
        parent::__construct(...$args);
    }
}
