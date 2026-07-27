@extends('backend.auth.partials.shell')
@section('title', 'Forgot password')

@section('card')
    <div class="auth-badge"><i class="feather-key"></i></div>

    <h4>Super admin password reset</h4>
    <p class="auth-sub">
        Enter the email on your super admin account and we will send a
        {{ config('security.otp.length', 6) }}-digit code.
    </p>

    <form action="{{ route('admin.password.email') }}" method="POST" novalidate>
        @csrf

        <div class="mb-4">
            <label class="form-label" for="email">Registered email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror"
                   placeholder="you@example.com" autocomplete="email" autofocus required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn btn-primary">Send reset code</button>
    </form>

    <div class="auth-note">
        <p>
            <strong>Not a super admin?</strong>
            Only super admins can reset their own password. Every other admin asks a super
            admin to issue a secure reset link.
        </p>
        <a href="{{ route('admin.password.assistance') }}" class="btn btn-soft-primary btn-sm">
            <i class="feather-help-circle"></i> Request password assistance
        </a>
    </div>

    <p class="auth-alt"><a href="{{ route('admin.login') }}">Back to sign in</a></p>
@endsection
