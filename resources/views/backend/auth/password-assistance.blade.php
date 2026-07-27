@extends('backend.auth.partials.shell')
@section('title', 'Request password assistance')
@section('card-class', 'auth-card-wide')

@section('card')
    <div class="auth-badge"><i class="feather-help-circle"></i></div>

    <h4>Request password assistance</h4>
    <p class="auth-sub">
        Admins other than super admins cannot reset their own password. Fill this in and a
        super admin will review it, then email you a secure single-use reset link.
    </p>

    <form action="{{ route('admin.password.assistance.store') }}" method="POST" novalidate>
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
                    Must be the exact address your admin account was created with — the super admin
                    uses it to confirm the request is really from you, and the reset link is sent
                    there and nowhere else.
                </small>
            </div>

            <div class="col-md-6">
                <label class="form-label" for="role">Your role</label>
                <select id="role" name="role" class="form-select @error('role') is-invalid @enderror">
                    <option value="">Select your role…</option>
                    @foreach (['Admin', 'Manager', 'Moderator', 'Staff', 'Support', 'Other'] as $role)
                        <option value="{{ $role }}" @selected(old('role') === $role)>{{ $role }}</option>
                    @endforeach
                </select>
                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label class="form-label" for="reason">Reason for request <span class="req">*</span></label>
                <textarea id="reason" name="reason" rows="3"
                          class="form-control @error('reason') is-invalid @enderror"
                          placeholder="e.g. I no longer have access to the device where my password was saved."
                          required>{{ old('reason') }}</textarea>
                @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label class="form-label" for="notes">Additional notes <span class="optional">(optional)</span></label>
                <textarea id="notes" name="notes" rows="2"
                          class="form-control @error('notes') is-invalid @enderror"
                          placeholder="Anything else the super admin should know.">{{ old('notes') }}</textarea>
                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-4">Send request to super admin</button>
    </form>

    <div class="auth-note">
        <p>
            <strong>Account deactivated instead?</strong>
            If you are being told your account is switched off, ask for it to be reactivated first.
        </p>
        <a href="{{ route('admin.activation.request') }}" class="btn btn-soft-primary btn-sm">
            <i class="feather-user-check"></i> Request account activation
        </a>
    </div>

    <p class="auth-alt"><a href="{{ route('admin.login') }}">Back to sign in</a></p>
@endsection
