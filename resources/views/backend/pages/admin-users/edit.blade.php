@extends('backend.master')
@section('title', 'Edit Admin')

@section('content')
<div class="container-fluid">

    <x-page-header
        title="Edit Admin"
        :subtitle="'Sign-in details and permissions for ' . $adminUser->name . '.'"
        icon="feather-user-check"
        :breadcrumb="['Administration' => null, 'Admin Users' => route('admin.admin-users.index'), $adminUser->name => null]">
        <a href="{{ route('admin.admin-users.index') }}" class="btn btn-soft">
            <i class="feather-arrow-left"></i> Back to Admins
        </a>
    </x-page-header>

    <div class="card">
        <div class="card-header">
            <h5>{{ $adminUser->name }}</h5>
            <span class="badge {{ $adminUser->isSuperadmin() ? 'bg-primary' : 'bg-secondary' }}">
                <i class="{{ $adminUser->isSuperadmin() ? 'feather-shield' : 'feather-user' }}"></i>
                {{ $adminUser->roleLabel() }}
            </span>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('admin.admin-users.update', $adminUser->id) }}">
                @csrf
                @method('PUT')
                @include('backend.pages.admin-users._form', ['isEdit' => true])
            </form>
        </div>
    </div>

</div>
@endsection
