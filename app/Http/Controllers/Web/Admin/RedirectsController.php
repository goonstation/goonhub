<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Redirect as ModelsRedirect;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RedirectsController extends Controller
{
    public function index(Request $request)
    {
        $redirects = ModelsRedirect::with([
            'createdByUser.gameAdmin',
            'updatedByUser.gameAdmin',
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
