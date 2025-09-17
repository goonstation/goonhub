<?php

namespace App\Attributes;

use Attribute;
use Dedoc\Scramble\Attributes\QueryParameter;

#[Attribute]
class HasGameAdminCkeyQuery extends QueryParameter
{
    public function __construct()
    {
        parent::__construct(
            'game_admin_ckey',
            type: 'string|null',
            description: 'The game admin ckey making this action. Required without game_admin_id.',
            example: 'wire',
        );
    }
}
