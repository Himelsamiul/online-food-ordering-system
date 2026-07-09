@extends('backend.master')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">Admin Users & Roles</h4>
        <a href="{{ route('admin.admin-users.create') }}" class="btn btn-primary btn-sm">
            ＋ New Admin
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Permissions</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admins as $admin)
                            <tr>
                                <td class="fw-semibold">
                                    {{ $admin->name }}
                                    @if($admin->id === auth()->id())
                                        <span class="badge bg-soft-primary text-primary">You</span>
                                    @endif
                                </td>
                                <td>{{ $admin->email }}</td>
                                <td>
                                    @if($admin->isSuperadmin())
                                        <span class="badge bg-danger">Superadmin</span>
                                    @else
                                        <span class="badge bg-secondary">Admin</span>
                                    @endif
                                </td>
                                <td style="max-width:340px">
                                    @if($admin->isSuperadmin())
                                        <span class="text-muted">Full access</span>
                                    @elseif($admin->permissions->isEmpty())
                                        <span class="text-danger">No access granted</span>
                                    @else
                                        @foreach($admin->permissions as $p)
                                            <span class="badge bg-soft-primary text-primary mb-1">{{ $p->label }}</span>
                                        @endforeach
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('admin.admin-users.edit', $admin->id) }}"
                                       class="btn btn-sm btn-outline-primary">Edit</a>

                                    @if($admin->id !== auth()->id())
                                        <form action="{{ route('admin.admin-users.delete', $admin->id) }}"
                                              method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No admin users.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
