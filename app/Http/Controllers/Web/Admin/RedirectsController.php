<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\Permissions\RedirectPermissions;
use App\Helpers\HasPermission;
use App\Http\Controllers\Controller;
use App\Models\Redirect as ModelsRedirect;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Inertia\Inertia;

class RedirectsController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            HasPermission::using(RedirectPermissions::VIEW, only: ['index']),
            HasPermission::using(RedirectPermissions::ADD, only: ['create', 'store']),
            HasPermission::using(RedirectPermissions::UPDATE, only: ['edit', 'update']),
            HasPermission::using(RedirectPermissions::DELETE, only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $redirects = ModelsRedirect::with([
            'createdByUser.gameAdmin.player',
            'updatedByUser.gameAdmin.player',
        ])->indexFilterPaginate(perPage: 30);

        if ($this->wantsInertia($request)) {
            return Inertia::render('Admin/Redirects/Index', [
                'redirects' => $redirects,
            ]);
        } else {
            return $redirects;
        }
    }

    public function create()
    {
        return Inertia::render('Admin/Redirects/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'from' => 'required|string',
            'to' => 'required|url',
        ]);

        $redirect = new ModelsRedirect;
        $redirect->from = $data['from'];
        $redirect->to = $data['to'];
        $redirect->created_by = $request->user()->id;
        $redirect->save();

        return to_route('admin.redirects.index');
    }

    public function edit(ModelsRedirect $redirect)
    {
        return Inertia::render('Admin/Redirects/Edit', [
            'redirect' => $redirect,
        ]);
    }

    public function update(Request $request, ModelsRedirect $redirect)
    {
        $data = $request->validate([
            'from' => 'required|string',
            'to' => 'required|url',
        ]);

        $redirect->from = $data['from'];
        $redirect->to = $data['to'];
        $redirect->updated_by = $request->user()->id;
        $redirect->save();

        return to_route('admin.redirects.index');
    }

    public function destroy(ModelsRedirect $redirect)
    {
        $redirect->delete();

        return ['message' => 'Redirect removed'];
    }
}
