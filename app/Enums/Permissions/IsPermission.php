<?php

namespace App\Enums\Permissions;

interface IsPermission
{
    public function label(): string;

    public function description(): string;
}
