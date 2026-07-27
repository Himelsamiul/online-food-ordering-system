<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Mail\AccountStatusMail;
use App\Models\Permission;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index()
    {
        $admins = User::admins()->with('permissions')->latest()->get();

        return view('backend.pages.admin-users.index', [
            'admins'  => $admins,
            'modules' => Permission::modules(),
            'stats'   => [
                'total'       => $admins->count(),
                'superadmins' => $admins->where('role', User::ROLE_SUPERADMIN)->count(),
                'admins'      => $admins->where('role', '!=', User::ROLE_SUPERADMIN)->count(),
                'inactive'    => $admins->where('is_active', false)->count(),
            ],
        ]);
    }

    public function create()
    {
        return view('backend.pages.admin-users.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $user = DB::transaction(function () use ($data, $request) {
            $user = User::create([
                'name'      => $data['name'],
                'username'  => $data['username'] ?: $this->uniqueUsername($data['email']),
                'email'     => $data['email'],
                'password'  => Hash::make($data['password']),
                'is_admin'  => true,
                'role'      => $data['role'],
                'is_active' => true,
            ]);

            $granted = $this->syncPermissions($user, $data['role'], $request->input('permissions', []));

            AuditLogger::system(
                AuditLogger::ACTION_ADMIN_CREATED,
                'Admin Users',
                'Created admin ' . $user->name . ' (' . $user->roleLabel() . ') with '
                    . count($granted) . ' permission(s)',
                $user,
                null,
                ['role' => $user->role, 'permissions' => implode(', ', $granted) ?: 'none'],
            );

            return $user;
        });

        return redirect()->route('admin.admin-users.index')
            ->with('success', 'Admin user "' . $user->name . '" created.');
    }

    public function edit(User $adminUser)
    {
        abort_unless($adminUser->is_admin, 404);

        return view('backend.pages.admin-users.edit', $this->formData([
            'adminUser' => $adminUser,
            'assigned'  => $adminUser->permissions->pluck('name')->all(),
        ]));
    }

    public function update(Request $request, User $adminUser)
    {
        abort_unless($adminUser->is_admin, 404);

        $data = $this->validated($request, $adminUser);

        // A superadmin must not demote or lock out the last superadmin.
        if ($adminUser->isSuperadmin() && $data['role'] !== User::ROLE_SUPERADMIN && $this->superadminCount() <= 1) {
            return back()->withInput()->with('error', 'You cannot demote the last superadmin.');
        }

        DB::transaction(function () use ($request, $adminUser, $data) {
            $before      = $adminUser->permissions->pluck('name')->all();
            $beforeRole  = $adminUser->role;

            $adminUser->name     = $data['name'];
            $adminUser->email    = $data['email'];
            $adminUser->role     = $data['role'];
            $adminUser->username = $data['username'] ?: $adminUser->username;

            if (!empty($data['password'])) {
                $adminUser->password            = Hash::make($data['password']);
                $adminUser->password_changed_at = now();
            }

            $adminUser->save();

            if (!empty($data['password'])) {
                AuditLogger::auth(
                    AuditLogger::ACTION_PASSWORD_CHANGED,
                    auth()->user()->name . ' changed the password for ' . $adminUser->email,
                    $adminUser,
                );
            }

            if ($beforeRole !== $adminUser->role) {
                AuditLogger::system(
                    AuditLogger::ACTION_ROLE_ASSIGNED,
                    'Admin Users',
                    $adminUser->name . ' changed from ' . $beforeRole . ' to ' . $adminUser->role,
                    $adminUser,
                    ['role' => $beforeRole],
                    ['role' => $adminUser->role],
                );
            }

            $after = $this->syncPermissions($adminUser, $data['role'], $request->input('permissions', []));

            $this->logPermissionDelta($adminUser, $before, $after);
        });

        return redirect()->route('admin.admin-users.index')
            ->with('success', 'Admin user updated.');
    }

    public function destroy(User $adminUser)
    {
        abort_unless($adminUser->is_admin, 404);

        if ($adminUser->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($adminUser->isSuperadmin() && $this->superadminCount() <= 1) {
            return back()->with('error', 'You cannot delete the last superadmin.');
        }

        $name  = $adminUser->name;
        $email = $adminUser->email;

        DB::transaction(function () use ($adminUser, $name, $email) {
            $adminUser->permissions()->detach();
            $adminUser->delete();

            AuditLogger::system(
                AuditLogger::ACTION_ADMIN_REMOVED,
                'Admin Users',
                'Deleted admin ' . $name . ' (' . $email . ')',
            );
        });

        return back()->with('success', 'Admin user deleted.');
    }

    /**
     * Switch an admin account on or off.
     *
     * A deactivated admin is blocked at login and pointed at the activation
     * request form, which a superadmin then reviews.
     */
    public function toggleStatus(Request $request, User $adminUser)
    {
        abort_unless($adminUser->is_admin, 404);

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        if ($adminUser->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        if ($adminUser->isSuperadmin() && $adminUser->isActive() && $this->activeSuperadminCount() <= 1) {
            return back()->with('error', 'You cannot deactivate the last active superadmin.');
        }

        $activating = !$adminUser->isActive();

        DB::transaction(function () use ($adminUser, $data, $activating) {
            $activating
                ? $adminUser->activate()
                : $adminUser->deactivate($data['reason'] ?? null);

            AuditLogger::system(
                $activating ? AuditLogger::ACTION_USER_ACTIVATED : AuditLogger::ACTION_USER_DEACTIVATED,
                'Admin Users',
                ($activating ? 'Activated' : 'Deactivated') . ' admin ' . $adminUser->email
                    . (!$activating && !empty($data['reason']) ? ' — ' . $data['reason'] : ''),
                $adminUser,
                ['is_active' => !$activating],
                ['is_active' => $activating],
            );
        });

        $sent = $this->notify($adminUser, $activating, $data['reason'] ?? null);

        return back()->with(
            'success',
            $adminUser->name . ' has been ' . ($activating ? 'activated' : 'deactivated') . '.'
                . ($sent ? '' : ' (the notification email could not be sent)')
        );
    }

    /* ------------------------------------------------------------ helpers */

    /** Everything both the create and edit forms need. */
    private function formData(array $extra = []): array
    {
        return array_merge([
            'groups'      => Permission::MODULES,
            'actions'     => Permission::ACTIONS,
            'actionIcons' => Permission::ACTION_ICONS,
            'assigned'    => [],
            'adminUser'   => null,
        ], $extra);
    }

    private function validated(Request $request, ?User $existing = null): array
    {
        return $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => [
                'nullable', 'string', 'max:255', 'alpha_dash',
                $existing
                    ? Rule::unique('users', 'username')->ignore($existing->id)
                    : Rule::unique('users', 'username'),
            ],
            'email' => [
                'required', 'email', 'max:255',
                $existing
                    ? Rule::unique('users', 'email')->ignore($existing->id)
                    : Rule::unique('users', 'email'),
            ],
            'password' => [
                $existing ? 'nullable' : 'required',
                'confirmed',
                'min:' . config('security.password_reset.min_password_length', 8),
            ],
            'role'          => ['required', Rule::in([User::ROLE_SUPERADMIN, User::ROLE_ADMIN])],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(Permission::names())],
        ]);
    }

    /**
     * Superadmins hold no explicit rows (they bypass every check); admins get
     * exactly what was ticked — plus the matching "view", because an admin who
     * can edit foods but cannot open the foods page is just a 403 waiting to
     * happen, and the sidebar entry is driven off view.
     *
     * @param  list<string>  $permissionNames
     * @return list<string>  what was actually granted
     */
    private function syncPermissions(User $user, string $role, array $permissionNames): array
    {
        if ($role === User::ROLE_SUPERADMIN) {
            $user->permissions()->sync([]);

            return [];
        }

        $names = collect($permissionNames)
            ->filter(fn ($name) => in_array($name, Permission::names(), true));

        $implied = $names
            ->map(fn ($name) => Permission::moduleOf($name) . '.view')
            ->filter(fn ($name) => in_array($name, Permission::names(), true));

        $final = $names->concat($implied)->unique()->values();

        $user->permissions()->sync(Permission::whereIn('name', $final)->pluck('id'));

        return $final->all();
    }

    /**
     * @param  list<string>  $before
     * @param  list<string>  $after
     */
    private function logPermissionDelta(User $user, array $before, array $after): void
    {
        $added   = array_values(array_diff($after, $before));
        $removed = array_values(array_diff($before, $after));

        if (!$added && !$removed) {
            return;
        }

        $parts = [];

        if ($added) {
            $parts[] = 'granted ' . implode(', ', array_slice($added, 0, 6))
                . (count($added) > 6 ? ' +' . (count($added) - 6) : '');
        }

        if ($removed) {
            $parts[] = 'revoked ' . implode(', ', array_slice($removed, 0, 6))
                . (count($removed) > 6 ? ' +' . (count($removed) - 6) : '');
        }

        AuditLogger::system(
            AuditLogger::ACTION_PERMISSION_CHANGED,
            'Permissions',
            'Permissions for ' . $user->name . ': ' . implode('; ', $parts),
            $user,
            ['permissions' => implode(', ', $before) ?: 'none'],
            ['permissions' => implode(', ', $after) ?: 'none'],
        );
    }

    private function notify(User $admin, bool $activated, ?string $reason): bool
    {
        try {
            Mail::to($admin->email)->queue(new AccountStatusMail(
                name:    $admin->name,
                email:   $admin->email,
                state:   $activated ? AccountStatusMail::ACTIVATED : AccountStatusMail::DEACTIVATED,
                note:    $reason,
                isAdmin: true,
            ));

            return true;
        } catch (\Throwable $e) {
            Log::warning('Admin status mail failed: ' . $e->getMessage());

            return false;
        }
    }

    private function uniqueUsername(string $email): string
    {
        $base = Str::slug(Str::before($email, '@'), '') ?: 'admin';
        $name = $base;
        $n    = 1;

        while (User::where('username', $name)->exists()) {
            $name = $base . (++$n);
        }

        return $name;
    }

    private function superadminCount(): int
    {
        return User::superadmins()->count();
    }

    private function activeSuperadminCount(): int
    {
        return User::superadmins()->active()->count();
    }
}
