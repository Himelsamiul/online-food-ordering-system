@extends('backend.auth.partials.shell')
@section('title', 'Choose a new password')

@section('card')
    <div class="auth-badge"><i class="feather-lock"></i></div>

    <h4>Choose a new password</h4>
    <p class="auth-sub">Setting a new password for <strong class="text-ink">{{ $email }}</strong>.</p>

    <form action="{{ route('admin.password.update') }}" method="POST" novalidate>
        @csrf

        @include('backend.auth.partials.password-fields')

        <button type="submit" class="btn btn-primary">Save new password</button>
    </form>

    <p class="auth-alt"><a href="{{ route('admin.login') }}">Cancel and sign in</a></p>
@endsection
