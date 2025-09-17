<?php

namespace App\Models\Traits;

use App\Services\CommonRequest;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

trait HasApiScope
{
    #[Scope]
    protected function forApi(Builder $query)
    {
        $commonRequest = app(CommonRequest::class);
        $fromServerId = $commonRequest->fromServerId();
        $fromServerGroup = $commonRequest->fromServerGroup();

        $query->whereNull(['server_id', 'server_group']);

        if ($fromServerId) {
            $query->orWhere('server_id', $fromServerId);
        }

        if ($fromServerGroup) {
            $query->orWhere('server_group', $fromServerGroup->id);
        }
    }
}
