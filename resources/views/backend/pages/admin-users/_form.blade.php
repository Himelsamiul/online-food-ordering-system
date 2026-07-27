{{--
    Shared by create + edit.

    Expects: $groups (group => module => meta), $actions, $actionIcons,
             $assigned (list of permission names), $adminUser (User|null),
             $isEdit (bool, optional — derived when missing).

    The matrix markup is driven by admin-refresh.js: it paints the checked
    tiles, runs the per-group "select all", enforces "any action implies
    view", and locks the whole grid off when the role is superadmin.
--}}
@php
    $adminUser = $adminUser ?? null;
    $isEdit    = $isEdit ?? (bool) $adminUser;
    $assigned  = $assigned ?? [];
    $selected  = (array) old('permissions', $assigned);
    $roleVal   = old('role', $adminUser->role ?? 'admin');
    $isSelf    = $adminUser && $adminUser->id === auth()->id();
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="adminName">Name</label>
        <input type="text" name="name" id="adminName"
               class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $adminUser->name ?? '') }}"
               placeholder="Full name" required>
        @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="adminUsername">Username</label>
        <input type="text" name="username" id="adminUsername"
               class="form-control @error('username') is-invalid @enderror"
               value="{{ old('username', $adminUser->username ?? '') }}"
               placeholder="Leave blank to generate one from the email">
        @error('username') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        <small class="form-text">
            Letters, numbers, dashes and underscores. An admin who is locked out types this on
            the password assistance form, so a super admin can check it against the account.
        </small>
    </div>

    <div class="col-md-6">
        <label class="form-label" for="adminEmail">Email</label>
        <input type="email" name="email" id="adminEmail"
               class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $adminUser->email ?? '') }}"
               placeholder="name@example.com" required>
        @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        <small class="form-text">
            The only address a reset link is ever sent to, and the one the assistance form is
            validated against.
        </small>
    </div>

    <div class="col-md-6">
        <label class="form-label" for="adminPassword">
            Password
            @if ($isEdit)
                <small class="text-muted">— leave blank to keep the current password</small>
            @endif
        </label>
        <input type="password" name="password" id="adminPassword"
               class="form-control @error('password') is-invalid @enderror"
               autocomplete="new-password"
               placeholder="{{ $isEdit ? 'Unchanged' : 'At least 6 characters' }}"
               {{ $isEdit ? '' : 'required' }}>
        @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="adminPasswordConfirm">Confirm Password</label>
        <input type="password" name="password_confirmation" id="adminPasswordConfirm"
               class="form-control" autocomplete="new-password"
               placeholder="{{ $isEdit ? 'Unchanged' : 'Repeat the password' }}"
               {{ $isEdit ? '' : 'required' }}>
    </div>
</div>

<hr class="hr-soft">

{{-- Role -------------------------------------------------------------- --}}
<p class="section-title">Role</p>

<div class="role-cards">
    <label class="role-card">
        <input type="radio" class="form-check-input" name="role" value="superadmin"
               @checked($roleVal === 'superadmin')>
        <span class="role-card-body">
            <span class="role-card-title"><i class="feather-shield"></i> Super Admin</span>
            <span class="role-card-text">
                Bypasses every permission check. Sees every module, every button and
                every admin account — including this screen.
            </span>
        </span>
    </label>

    <label class="role-card">
        <input type="radio" class="form-check-input" name="role" value="admin"
               @checked($roleVal !== 'superadmin')>
        <span class="role-card-body">
            <span class="role-card-title"><i class="feather-user"></i> Admin</span>
            <span class="role-card-text">
                Starts with nothing. Gets exactly the modules and actions ticked in
                the matrix below, and nothing else.
            </span>
        </span>
    </label>
</div>

@error('role') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

@if ($isSelf)
    <p class="perm-note perm-note-warn">
        <i class="feather-alert-triangle"></i>
        This is your own account. Changing your role or removing rights takes effect
        the moment you save.
    </p>
@endif

<hr class="hr-soft">

{{-- Permission matrix -------------------------------------------------- --}}
<div class="perm-toolbar">
    <div class="perm-toolbar-text">
        <p class="section-title mb-1">Permissions</p>
        <p class="perm-note">
            Ticking Create, Edit or Delete also ticks View, because View is what puts the
            module in the sidebar and lets the page open at all. Untick View and the whole
            module goes with it.
        </p>
    </div>

    <div class="action-group">
        <button type="button" class="btn btn-soft-primary btn-sm" data-perm-all>
            <i class="feather-check-square"></i> Grant all
        </button>
        <button type="button" class="btn btn-soft btn-sm" data-perm-none>
            <i class="feather-square"></i> Revoke all
        </button>
    </div>
</div>

<div class="alert alert-info" data-perm-superadmin-notice style="display:none">
    <i class="feather-shield"></i>
    A super admin already holds everything, so the matrix is disabled and nothing ticked
    here is saved. Switch the role to Admin to pick individual rights.
</div>

<div data-perm-matrix>
    @foreach ($groups as $group => $groupModules)
        <div class="perm-group">
            <div class="perm-group-head">
                <h6>{{ $group }}</h6>
                <label class="form-check">
                    <input type="checkbox" class="form-check-input" data-perm-group-toggle>
                    <span>Select all</span>
                </label>
            </div>

            <div class="perm-head-row">
                <span>Module</span>
                @foreach (['view', 'create', 'edit', 'delete'] as $action)
                    <span>{{ $actions[$action] }}</span>
                @endforeach
            </div>

            @foreach ($groupModules as $module => $meta)
                <div class="perm-row">
                    <div class="perm-module">
                        <i class="{{ $meta['icon'] }}"></i>{{ $meta['label'] }}
                    </div>

                    @foreach (['view', 'create', 'edit', 'delete'] as $action)
                        @if (in_array($action, $meta['actions']))
                            <label class="perm-check action-{{ $action }}"
                                   title="{{ \App\Models\Permission::actionHint($module, $action) }}">
                                <input type="checkbox" class="form-check-input" name="permissions[]"
                                       value="{{ $module }}.{{ $action }}"
                                       @checked(in_array("$module.$action", $selected))>
                                <span><i class="{{ $actionIcons[$action] }}"></i> {{ $actions[$action] }}</span>
                            </label>
                        @else
                            <div class="perm-na">n/a</div>
                        @endif
                    @endforeach
                </div>
            @endforeach
        </div>
    @endforeach
</div>

@error('permissions') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

<div class="form-actions">
    <button type="submit" class="btn btn-primary">
        <i class="feather-save"></i> {{ $isEdit ? 'Save Changes' : 'Create Admin' }}
    </button>
    <a href="{{ route('admin.admin-users.index') }}" class="btn btn-soft">Cancel</a>
</div>

@push('styles')
<style>
    /* Role picker ---------------------------------------------------- */
    .role-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 14px;
    }

    .role-card { position: relative; display: block; margin: 0; cursor: pointer; }

    .role-card input {
        position: absolute;
        top: 17px;
        left: 16px;
        margin: 0;
        cursor: pointer;
    }

    .role-card-body {
        display: block;
        padding: 15px 17px 15px 45px;
        border: 1px solid var(--ar-line);
        border-radius: var(--ar-radius);
        background: var(--ar-surface);
        transition: border-color .16s, background-color .16s, box-shadow .16s;
        height: 100%;
    }

    .role-card:hover .role-card-body { border-color: var(--ar-primary); }

    .role-card input:checked ~ .role-card-body {
        border-color: var(--ar-primary);
        background: var(--ar-primary-soft);
        box-shadow: 0 0 0 1px var(--ar-primary);
    }

    .role-card-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 700;
        color: var(--ar-ink);
        margin-bottom: 5px;
    }

    .role-card-title i { color: var(--ar-primary); }

    .role-card-text {
        display: block;
        font-size: 12.5px;
        line-height: 1.55;
        color: var(--ar-muted);
    }

    /* Matrix toolbar -------------------------------------------------- */
    .perm-toolbar {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .perm-toolbar-text { max-width: 68ch; }

    .perm-note {
        font-size: 12.5px;
        line-height: 1.55;
        color: var(--ar-muted);
        margin: 0;
    }

    .perm-note-warn {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        margin-top: 12px;
        color: var(--ar-warning);
    }

    [data-perm-superadmin-notice] {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        font-size: 13px;
    }

    /* Per-group "select all" sits in the group header, not a Bootstrap row. */
    .perm-group-head .form-check {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 0;
        margin: 0;
        min-height: 0;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--ar-muted);
        cursor: pointer;
        white-space: nowrap;
    }

    .perm-group-head .form-check input { margin: 0; float: none; cursor: pointer; }

    .perm-check span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-width: 0;
    }

    .perm-check span i { font-size: 12px; flex-shrink: 0; }

    .form-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 22px;
        padding-top: 18px;
        border-top: 1px solid var(--ar-line);
    }
</style>
@endpush
