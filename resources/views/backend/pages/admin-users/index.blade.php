@extends('backend.master')
@section('title', 'Admin Users')

@section('content')
<div class="container-fluid">

    @php
        /**
         * The controller refuses to delete your own account or the final
         * superadmin, so the buttons for those two cases are never drawn.
         */
        $currentId       = auth()->id();
        $superadminCount = $admins->where('role', 'superadmin')->count();
        $moduleTotal     = count($modules);
    @endphp

    <x-page-header
        title="Admin Users"
        subtitle="Who can sign in to the panel, and exactly what each of them is allowed to do."
        icon="feather-user-check"
        :breadcrumb="['Administration' => null, 'Admin Users' => null]">
        @can('manage-admins')
            <a href="{{ route('admin.admin-users.create') }}" class="btn btn-primary">
                <i class="feather-plus"></i> Add Admin
            </a>
        @endcan
    </x-page-header>

    <div class="stat-grid">
        <x-stat-card label="Total Admins" :value="$stats['total']" icon="feather-users"
                     tone="primary" foot="accounts that can sign in" />
        <x-stat-card label="Super Admins" :value="$stats['superadmins']" icon="feather-shield"
                     tone="warning" foot="bypass every permission check" />
        <x-stat-card label="Standard Admins" :value="$stats['admins']" icon="feather-user"
                     tone="info" foot="limited to what was granted" />
    </div>

    @if ($admins->isEmpty())
        <div class="card">
            <div class="card-body">
                <x-empty-state icon="feather-user-x" title="No admin users yet"
                               message="Create the first account and pick exactly which modules it can reach.">
                    @can('manage-admins')
                        <a href="{{ route('admin.admin-users.create') }}" class="btn btn-primary">
                            <i class="feather-plus"></i> Add Admin
                        </a>
                    @endcan
                </x-empty-state>
            </div>
        </div>
    @else
        <p class="section-title">All Admins &middot; {{ $admins->count() }}</p>

        <div class="admin-grid">
            @foreach ($admins as $admin)
                @php
                    $initials = collect(preg_split('/\s+/', trim((string) $admin->name)))
                        ->filter()
                        ->take(2)
                        ->map(fn ($part) => mb_substr($part, 0, 1))
                        ->implode('');

                    $isSelf     = $admin->id === $currentId;
                    $isSuper    = $admin->isSuperadmin();
                    $lastSuper  = $isSuper && $superadminCount <= 1;
                    $canDelete  = !$isSelf && !$lastSuper;
                    $heldCount  = $admin->permissions->count();
                    $liveModules = collect($modules)
                        ->filter(fn ($meta, $key) => $admin->moduleGrantCount($key) > 0)
                        ->count();
                @endphp

                <div class="card admin-card">
                    <div class="card-body">

                        <div class="admin-card-head">
                            <div class="cell-media">
                                <span class="avatar-initials">{{ $initials !== '' ? $initials : '?' }}</span>
                                <div>
                                    <p class="cell-title">
                                        {{ $admin->name }}
                                        @if ($isSelf)
                                            <span class="badge bg-info">You</span>
                                        @endif
                                    </p>
                                    <p class="cell-sub">{{ $admin->email }}</p>
                                </div>
                            </div>

                            <span class="badge {{ $isSuper ? 'bg-primary' : 'bg-secondary' }}">
                                <i class="{{ $isSuper ? 'feather-shield' : 'feather-user' }}"></i>
                                {{ $admin->roleLabel() }}
                            </span>
                        </div>

                        <div class="admin-card-body">
                            @if ($isSuper)
                                <p class="admin-perm-note">Access</p>
                                <div class="chip-row">
                                    <span class="chip active">
                                        <i class="feather-unlock"></i> Full access to everything
                                    </span>
                                </div>
                            @else
                                <p class="admin-perm-note">Access</p>

                                <p class="admin-perm-count {{ $heldCount ? '' : 'text-danger' }}">
                                    {{ $heldCount }} permission{{ $heldCount === 1 ? '' : 's' }}
                                    across {{ $liveModules }} of {{ $moduleTotal }} modules.
                                    @if ($heldCount === 0)
                                        Nothing granted yet — this admin signs in to an empty sidebar.
                                    @endif
                                </p>

                                <div class="chip-row perm-summary">
                                    @foreach ($modules as $key => $meta)
                                        @php $count = $admin->moduleGrantCount($key); @endphp
                                        <span class="chip {{ $count > 0 ? 'active' : '' }}"
                                              title="{{ $meta['label'] }} — {{ $count }} of {{ count($meta['actions']) }} actions granted">
                                            <i class="{{ $meta['icon'] }}"></i>
                                            {{ $meta['label'] }}
                                            <b>{{ $count }}/{{ count($meta['actions']) }}</b>
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="admin-card-foot">
                            <span class="admin-meta">
                                @if ($isSelf)
                                    Your own account
                                @elseif ($lastSuper)
                                    The last super admin
                                @else
                                    &nbsp;
                                @endif
                            </span>

                            <div class="action-group">
                                @can('manage-admins')
                                    <a href="{{ route('admin.admin-users.edit', $admin->id) }}"
                                       class="btn btn-soft-primary btn-sm">
                                        <i class="feather-edit-2"></i> Edit
                                    </a>
                                @endcan

                                @can('manage-admins')
                                    @if ($canDelete)
                                        <form action="{{ route('admin.admin-users.delete', $admin->id) }}"
                                              method="POST" class="delete-form"
                                              data-confirm-title="Delete {{ $admin->name }}?"
                                              data-confirm-text="The account and every permission granted to it are removed."
                                              data-confirm-button="Yes, delete admin">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-soft-danger btn-sm">
                                                <i class="feather-trash-2"></i> Delete
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                            </div>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection

@push('styles')
<style>
    .admin-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
        gap: 16px;
    }

    .admin-card .card-body {
        display: flex;
        flex-direction: column;
        gap: 16px;
        height: 100%;
    }

    .admin-card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
    }

    /* The stacked-table rule right-aligns .cell-media on small screens; inside
       a card the identity block must stay on the left. */
    .admin-card .cell-media { justify-content: flex-start; }

    .admin-card-body { flex: 1 1 auto; }

    .admin-perm-note {
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--ar-muted);
        margin: 0 0 9px;
    }

    .admin-perm-count {
        font-size: 12.5px;
        line-height: 1.55;
        color: var(--ar-ink-2);
        margin: 0 0 11px;
    }

    .perm-summary .chip { gap: 5px; }
    .perm-summary .chip b { font-weight: 800; font-variant-numeric: tabular-nums; }
    .perm-summary .chip i { font-size: 12px; opacity: .85; }

    .admin-card-foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        padding-top: 14px;
        border-top: 1px solid var(--ar-line);
    }

    .admin-meta { font-size: 12.5px; color: var(--ar-muted); }

    @media (max-width: 575.98px) {
        .admin-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush
