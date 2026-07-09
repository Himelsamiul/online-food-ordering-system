<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    /**
     * List all admin panel users.
     */
    public function index()
    {
        $admins = User::where('is_admin', true)
            ->with('permissions')
            ->latest()
            ->get();

        return view('backend.pages.admin-users.index', compact('admins'));
    }

    public function create()
    {
        $groups = Permission::CATALOG;
        return view('backend.pages.admin-users.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|min:4|confirmed',
            'role'          => ['required', Rule::in(['superadmin', 'admin'])],
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'is_admin' => true,
            'role'     => $data['role'],
        ]);

        $this->syncPermissions($user, $data['role'], $request->input('permissions', []));

        return redirect()->route('admin.admin-users.index')
            ->with('success', 'Admin user created successfully.');
    }

    public function edit(User $adminUser)
    {
        abort_unless($adminUser->is_admin, 404);

        $groups   = Permission::CATALOG;
        $assigned = $adminUser->permissions->pluck('name')->all();

        return view('backend.pages.admin-users.edit', compact('adminUser', 'groups', 'assigned'));
    }

    public function update(Request $request, User $adminUser)
    {
        abort_unless($adminUser->is_admin, 404);

        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => ['required', 'email', Rule::unique('users', 'email')->ignore($adminUser->id)],
            'password'      => 'nullable|min:4|confirmed',
            'role'          => ['required', Rule::in(['superadmin', 'admin'])],
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        // Guard: a superadmin must not demote / lock out the last superadmin.
        if ($adminUser->isSuperadmin() && $data['role'] !== 'superadmin' && $this->superadminCount() <= 1) {
            return back()->withInput()
                ->with('error', 'You cannot demote the last superadmin.');
        }

        $adminUser->name  = $data['name'];
        $adminUser->email = $data['email'];
        $adminUser->role  = $data['role'];
        if (!empty($data['password'])) {
            $adminUser->password = Hash::make($data['password']);
        }
        $adminUser->save();

        $this->syncPermissions($adminUser, $data['role'], $request->input('permissions', []));

        return redirect()->route('admin.admin-users.index')
            ->with('success', 'Admin user updated successfully.');
    }

    public function destroy(User $adminUser)
    {
        abort_unless($adminUser->is_admin, 404);

        // Can't delete yourself.
        if ($adminUser->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Can't delete the last superadmin.
        if ($adminUser->isSuperadmin() && $this->superadminCount() <= 1) {
            return back()->with('error', 'You cannot delete the last superadmin.');
        }

        $adminUser->permissions()->detach();
        $adminUser->delete();

        return back()->with('success', 'Admin user deleted successfully.');
    }

    /**
     * Superadmins hold no explicit rows (they bypass every check);
     * admins get exactly the granted permissions.
     */
    private function syncPermissions(User $user, string $role, array $permissionNames): void
    {
        if ($role === 'superadmin') {
            $user->permissions()->sync([]);
            return;
        }

        $ids = Permission::whereIn('name', $permissionNames)->pluck('id');
        $user->permissions()->sync($ids);
    }

    private function superadminCount(): int
    {
        return User::where('role', 'superadmin')->count();
    }
}
