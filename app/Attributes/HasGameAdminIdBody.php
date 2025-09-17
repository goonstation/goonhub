<?php

namespace App\Attributes;

use Attribute;
use Dedoc\Scramble\Attributes\BodyParameter;

#[Attribute]
class HasGameAdminIdBody extends BodyParameter
{
    public function __construct()
    {
        parent::__construct(
            'game_admin_id',
            type: 'integer|null',
            description: 'The game admin ID making this action. Required without game_admin_ckey.',
            example: 1,
        );
    }
}
