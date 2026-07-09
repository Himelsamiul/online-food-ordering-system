@extends('backend.master')

@section('content')

<div class="container-fluid">
    <h4 class="fw-bold mb-3">Edit Admin — {{ $adminUser->name }}</h4>

    <div class="card">
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
