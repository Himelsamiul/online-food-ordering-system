@extends('backend.auth.partials.shell')
@section('title', 'Set your password')

@section('card')
    <div class="auth-badge"><i class="feather-lock"></i></div>

    <h4>Set your password</h4>
    <p class="auth-sub">
        A super admin approved your request. Choose a password for
        <strong class="text-ink">{{ $email }}</strong>.
    </p>

    <form action="{{ route('admin.password.reset.link.update') }}" method="POST" novalidate>
        @csrf
        <input type="hidden" name="email" value="{{ $email }}">
        <input type="hidden" name="token" value="{{ $token }}">

        @include('backend.auth.partials.password-fields')

        <button type="submit" class="btn btn-primary">Save password and continue</button>
    </form>

    <div class="auth-note">
        <p>
            This link works once. Once you save, it stops working and your old password —
            if you still had one — no longer signs you in.
        </p>
    </div>
@endsection
