<?php

namespace App\Http\Controllers\Web\Admin;

use App\Helpers\GetPermissionData;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Laravel\Jetstream\Jetstream;
use Spatie\Permission\Models\Permission;
use Str;

class ApiTokenController extends Controller
{
    /**
     * Show the user API token screen.
     *
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        return Inertia::render('API/Index', [
            'tokens' => $request->user()->tokens->map(function ($token) {
                return $token->toArray() + [
                    'last_used_ago' => optional($token->last_used_at)->diffForHumans(),
                    'abilities' => $token->abilities ?? [],
                ];
            }),
        ]);
    }

    /**
     * Show the create API token screen.
     *
     * @return \Inertia\Response
     */
    public function create(Request $request)
    {
        $permissions = Permission::where('guard_name', 'web')->get()
            ->pluck('name')
            ->groupBy(function ($permission) {
                return Str::title(implode(' ', array_slice(explode('-', $permission), 1)));
            })
            ->sortKeys()
            ->map(function (Collection $permissions, $group) {
                return [
                    'group' => $group,
                    'permissions' => $permissions->map(function ($permission) {
                        $data = GetPermissionData::getPermissionData($permission);

                        return [
                            'label' => $data['label'],
                            'description' => $data['description'],
                            'value' => $permission,
                        ];
                    }),
                ];
            })
            ->values();

        return Inertia::render('API/Create', [
            'availablePermissions' => $permissions,
        ]);
    }

    /**
     * Create a new API token.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'for_game_server' => ['boolean'],
        ]);

        /** @var \App\Models\User */
        $user = $request->user();

        $token = $user->createToken(
            $request->name,
            Jetstream::validPermissions($request->input('permissions', []))
        );

        if ($user->isAdmin()) {
            $token->accessToken->update([
                'for_game_server' => $request->input('for_game_server', false),
            ]);
        }

        return back()->with('flash', [
            'token' => explode('|', $token->plainTextToken, 2)[1],
        ]);
    }

    /**
     * Show the edit API token screen.
     *
     * @param  string  $tokenId
     * @return \Inertia\Response
     */
    public function edit(Request $request, $tokenId)
    {
        $token = $request->user()->tokens()->where('id', $tokenId)->firstOrFail();

        $permissions = Permission::where('guard_name', 'web')->get()
            ->pluck('name')
            ->groupBy(function ($permission) {
                return Str::title(implode(' ', array_slice(explode('-', $permission), 1)));
            })
            ->sortKeys()
            ->map(function (Collection $permissions, $group) {
                return [
                    'group' => $group,
                    'permissions' => $permissions->map(function ($permission) {
                        $data = GetPermissionData::getPermissionData($permission);

                        return [
                            'label' => $data['label'],
                            'description' => $data['description'],
                            'value' => $permission,
                        ];
                    }),
                ];
            })
            ->values();

        return Inertia::render('API/Edit', [
            'token' => [
                'id' => $token->id,
                'name' => $token->name,
                'abilities' => $token->abilities ?? [],
            ],
            'availablePermissions' => $permissions,
        ]);
    }

    /**
     * Update the given API token's permissions.
     *
     * @param  string  $tokenId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $tokenId)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'permissions' => 'array',
            'permissions.*' => 'string',
        ]);

        /** @var \App\Models\PersonalAccessToken */
        $token = $request->user()->tokens()->where('id', $tokenId)->firstOrFail();

        $token->forceFill([
            'name' => $request->input('name'),
            'abilities' => Jetstream::validPermissions($request->input('permissions', [])),
        ])->save();

        return back(303)->with('success', 'API token updated successfully.');
    }

    /**
     * Delete the given API token.
     *
     * @param  string  $tokenId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, $tokenId)
    {
        $request->user()->tokens()->where('id', $tokenId)->first()->delete();

        return back(303);
    }
}
