<?php

namespace App\Models;

use App\Models\Traits\IndexFilterScope;
use EloquentFilter\Filterable;
use Spatie\Permission\Models\Role as ModelsRole;

class Role extends ModelsRole
{
    use Filterable;
    use IndexFilterScope;
}
