@extends('backend.master')
@section('title', 'Add Admin')

@section('content')
<div class="container-fluid">

    <x-page-header
        title="Add Admin"
        subtitle="Create the account, then tick exactly what it may reach."
        icon="feather-user-plus"
        :breadcrumb="['Administration' => null, 'Admin Users' => route('admin.admin-users.index'), 'Add Admin' => null]">
        <a href="{{ route('admin.admin-users.index') }}" class="btn btn-soft">
            <i class="feather-arrow-left"></i> Back to Admins
        </a>
    </x-page-header>

    <div class="card">
        <div class="card-header">
            <h5>New Admin Account</h5>
            <span class="text-muted fs-13">Sign-in details and permissions</span>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('admin.admin-users.store') }}">
                @csrf
                @include('backend.pages.admin-users._form', ['isEdit' => false])
            </form>
        </div>
    </div>

</div>
@endsection
