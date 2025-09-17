<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlayerAdmin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('teams')->indexFilterPaginate(perPage: 30);

        if ($this->wantsInertia($request)) {
            return Inertia::render('Admin/Users/Index', [
                'users' => $users,
            ]);
        } else {
            return $users;
        }
    }

    public function create()
    {
        return Inertia::render('Admin/Users/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
            'game_admin_id' => 'nullable',
            'is_admin' => 'boolean',
        ]);

        $user = new User;
        $user->name = $data['name'];
        $user->email = $data['email'];
        // $user->player_admin_id = isset($data['game_admin_id']) ? $data['game_admin_id'] : null;
        $user->password = Hash::make($data['password']);

        // TODO: create or find a Player, associate it with the User and the PlayerAdmin

        // Only current admins can modify other admin status
        if ($request->user()->isAdmin()) {
            $user->is_admin = $data['is_admin'];
        }

        $user->save();

        return to_route('admin.users.index');
    }

    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'editUser' => $user->only([
                'id',
                'name',
                'email',
                'profile_photo_url',
                'is_admin',
                'game_admin_id',
            ]),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'nullable',
            'confirm_password' => 'required_with:password|same:password',
            'game_admin_id' => 'nullable',
            'is_admin' => 'boolean',
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        // $user->game_admin_id = isset($data['game_admin_id']) ? $data['game_admin_id'] : null;

        // TODO: update the PlayerAdmin

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        // Only current admins can modify other admin status
        $authUser = $request->user();
        if ($authUser->isAdmin() && $authUser->id !== $user->id) {
            $user->is_admin = $data['is_admin'];
        }

        $user->save();

        return redirect()->back();
    }
}
