<?php

namespace App\Attributes;

use Attribute;
use Dedoc\Scramble\Attributes\QueryParameter;

#[Attribute]
class HasServerFilter extends QueryParameter
{
    public function __construct()
    {
        parent::__construct(
            'filters.server',
            type: 'string',
            description: 'The server ID to filter by.',
            example: 'main1',
            infer: false,
        );
    }
}
