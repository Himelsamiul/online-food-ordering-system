@extends('backend.master')

@section('content')

<div class="container-fluid">
    <h4 class="fw-bold mb-3">New Admin User</h4>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.admin-users.store') }}">
                @csrf
                @include('backend.pages.admin-users._form', ['isEdit' => false, 'assigned' => []])
            </form>
        </div>
    </div>
</div>

@endsection
