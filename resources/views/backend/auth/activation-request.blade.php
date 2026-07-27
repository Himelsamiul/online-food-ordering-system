@extends('backend.auth.partials.shell')
@section('title', 'Request account activation')
@section('card-class', 'auth-card-wide')

@section('card')
    <div class="auth-badge tone-warning"><i class="feather-user-check"></i></div>

    <h4>Request account activation</h4>
    <p class="auth-sub">
        Your admin account has been deactivated, so signing in is blocked. Send a super admin
        this form and they will review it.
    </p>

    <form action="{{ route('admin.activation.request.store') }}" method="POST" novalidate>
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="name">Full name <span class="req">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name') }}"
                       class="form-control @error('name') is-invalid @enderror"
                       placeholder="As it appears on your account" required autofocus>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="username">Username <span class="req">*</span></label>
                <input type="text" id="username" name="username" value="{{ old('username') }}"
                       class="form-control @error('username') is-invalid @enderror"
                       placeholder="Your admin username" required>
                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label class="form-label" for="email">Registered email address <span class="req">*</span></label>
                <input type="email" id="email" name="email"
                       value="{{ old('email', $prefillEmail) }}"
                       class="form-control @error('email') is-invalid @enderror"
                       placeholder="you@example.com" autocomplete="email" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="form-text">
                    Must be the exact address on the deactivated account. The decision is emailed there.
                </small>
            </div>

            <div class="col-12">
                <label class="form-label" for="reason">Reason for activation <span class="req">*</span></label>
                <textarea id="reason" name="reason" rows="3"
                          class="form-control @error('reason') is-invalid @enderror"
                          placeholder="Why the account should be switched back on."
                          required>{{ old('reason') }}</textarea>
                @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label class="form-label" for="notes">Additional notes <span class="optional">(optional)</span></label>
                <textarea id="notes" name="notes" rows="2"
                          class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-4">Send activation request</button>
    </form>

    <p class="auth-alt">
        <a href="{{ route('admin.password.assistance') }}">I just need a password reset</a>
        &nbsp;·&nbsp;
        <a href="{{ route('admin.login') }}">Back to sign in</a>
    </p>
@endsection
