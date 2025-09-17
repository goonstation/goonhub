<?php

namespace App\Attributes;

use Attribute;
use Dedoc\Scramble\Attributes\QueryParameter;

#[Attribute]
class HasServerIdQuery extends QueryParameter
{
    public function __construct()
    {
        parent::__construct(
            'server_id',
            type: 'string|null',
            description: 'The server ID to target.',
            example: 'main1',
        );
    }
}
