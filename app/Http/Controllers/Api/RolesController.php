<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permissions\RolePermissions;
use App\Helpers\HasAnyAbility;
use App\Http\Controllers\Controller;
use App\Http\Requests\Roles\IndexRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controllers\HasMiddleware;

class RolesController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            HasAnyAbility::using(RolePermissions::VIEW, only: ['index']),
        ];
    }

    /**
     * List
     *
     * List paginated and filtered roles
     *
     * @return AnonymousResourceCollection<LengthAwarePaginator<RoleResource>>
     */
    public function index(IndexRequest $request)
    {
        return RoleResource::collection(
            Role::indexFilterPaginate()
        );
    }
}
