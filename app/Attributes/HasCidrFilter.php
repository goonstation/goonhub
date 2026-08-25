<?php

namespace App\Attributes;

use Attribute;
use Dedoc\Scramble\Attributes\QueryParameter;

#[Attribute]
class HasCidrFilter extends QueryParameter
{
    public function __construct()
    {
        parent::__construct(
            'filters.ip',
            type: 'string',
            description: 'The IPv4 CIDR to filter by.',
            example: '192.168.1.0/24',
            infer: false,
        );
    }
}
