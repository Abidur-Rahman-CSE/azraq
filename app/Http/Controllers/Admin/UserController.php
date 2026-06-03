<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $users = User::query()
            ->when($search !== '', fn ($query) => $query
                ->where('name', 'like', '%'.$search.'%')
                ->orWhere('email', 'like', '%'.$search.'%'))
            ->orderByDesc('is_admin')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search'));
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'user' => new User(['is_admin' => false, 'role' => User::ROLE_CUSTOMER]),
            'roleOptions' => User::roleOptions(),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_admin'] = in_array($data['role'], User::adminRoles(), true);

        User::create($data);

        return redirect()->route('admin.users.index')->with('status', 'User created.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
            'roleOptions' => User::roleOptions(),
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $data['is_admin'] = in_array($data['role'], User::adminRoles(), true);

        if ($user->is(auth()->user()) && ! $data['is_admin']) {
            return back()
                ->withErrors(['role' => 'You cannot remove admin access from your own account.'])
                ->withInput();
        }

        if ($user->isSuperAdmin() && $data['role'] !== User::ROLE_SUPER_ADMIN && ! $this->anotherSuperAdminExists($user)) {
            return back()
                ->withErrors(['role' => 'At least one super admin account must remain.'])
                ->withInput();
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('status', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('status', 'You cannot delete your own user account.');
        }

        if ($user->isSuperAdmin() && ! $this->anotherSuperAdminExists($user)) {
            return back()->with('status', 'At least one admin account must remain.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'User deleted.');
    }

    private function anotherSuperAdminExists(User $user): bool
    {
        return User::query()
            ->where('role', User::ROLE_SUPER_ADMIN)
            ->whereKeyNot($user->id)
            ->exists();
    }
}
