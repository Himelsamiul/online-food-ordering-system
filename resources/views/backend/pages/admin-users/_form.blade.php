{{-- Shared fields for create/edit. Expects: $adminUser (nullable), $groups, $assigned (array), $isEdit --}}
@php
    $adminUser = $adminUser ?? null;
    $assigned  = old('permissions', $assigned ?? []);
    $roleVal   = old('role', $adminUser->role ?? 'admin');
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
    </div>
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control"
               value="{{ old('name', $adminUser->name ?? '') }}" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control"
               value="{{ old('email', $adminUser->email ?? '') }}" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">
            Password @if($isEdit)<small class="text-muted">(leave blank to keep current)</small>@endif
        </label>
        <input type="password" name="password" class="form-control" {{ $isEdit ? '' : 'required' }}>
    </div>

    <div class="col-md-6">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="password_confirmation" class="form-control" {{ $isEdit ? '' : 'required' }}>
    </div>

    <div class="col-md-6">
        <label class="form-label">Role</label>
        <select name="role" id="roleSelect" class="form-select" onchange="togglePerms()">
            <option value="admin" {{ $roleVal === 'admin' ? 'selected' : '' }}>Admin (limited)</option>
            <option value="superadmin" {{ $roleVal === 'superadmin' ? 'selected' : '' }}>Superadmin (full access)</option>
        </select>
    </div>
</div>

{{-- Permissions --}}
<div id="permsWrap" class="mt-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-bold mb-0">Permissions</h6>
        <div>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="checkAllPerms(true)">Select all</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="checkAllPerms(false)">Clear</button>
        </div>
    </div>

    <div class="row g-3">
        @foreach($groups as $groupName => $items)
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="fw-semibold text-primary mb-2">{{ $groupName }}</div>
                        @foreach($items as $name => $label)
                            <div class="form-check">
                                <input class="form-check-input perm-check" type="checkbox"
                                       name="permissions[]" value="{{ $name }}"
                                       id="perm_{{ $name }}"
                                       {{ in_array($name, $assigned) ? 'checked' : '' }}>
                                <label class="form-check-label" for="perm_{{ $name }}">{{ $label }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <small class="text-muted d-block mt-2" id="superNote" style="display:none">
        Superadmin already has full access — individual permissions are ignored.
    </small>
</div>

<div class="mt-4">
    <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update Admin' : 'Create Admin' }}</button>
    <a href="{{ route('admin.admin-users.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

<script>
    function togglePerms() {
        const role = document.getElementById('roleSelect').value;
        const wrap = document.getElementById('permsWrap');
        const isSuper = role === 'superadmin';
        wrap.querySelectorAll('.perm-check').forEach(c => c.disabled = isSuper);
        wrap.style.opacity = isSuper ? '0.45' : '1';
        document.getElementById('superNote').style.display = isSuper ? 'block' : 'none';
    }
    function checkAllPerms(val) {
        document.querySelectorAll('.perm-check').forEach(c => { if (!c.disabled) c.checked = val; });
    }
    togglePerms();
</script>
