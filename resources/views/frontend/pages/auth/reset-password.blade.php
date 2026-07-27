@extends('frontend.master')
@section('title', 'Set a New Password')

@push('styles')
<style>
    .sf-otp {
        font-family: 'Courier New', Courier, monospace;
        font-size: 26px;
        font-weight: 700;
        letter-spacing: 12px;
        text-indent: 12px; /* balances the trailing letter-space so it looks centred */
        text-align: center;
        padding: 12px;
        height: auto;
    }

    .sf-otp::placeholder {
        font-size: 15px;
        letter-spacing: 2px;
        font-weight: 500;
    }

    .sf-expiry {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin: 12px 0 20px;
        padding: 10px 14px;
        border-radius: var(--sf-radius-sm);
        background: rgba(241, 184, 22, .1);
        border: 1px solid rgba(241, 184, 22, .3);
        color: rgba(255, 255, 255, .84);
        font-size: 13px;
    }

    .sf-expiry i { color: var(--sf-accent); }
</style>
@endpush

@section('content')

<section class="registration-section sf-auth-wrap">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="sf-auth-card">

                    <div class="sf-request-badge">
                        <i class="fa fa-key" aria-hidden="true"></i>
                    </div>

                    <h3>Set a new password</h3>
                    <p class="sf-auth-sub">
                        Enter the code we sent to <strong>{{ $email }}</strong>, then choose the
                        password you want to use from now on.
                    </p>

                    <form action="{{ route('password.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="email" value="{{ $email }}">

                        <div class="form-group mb-2">
                            <label for="code">Reset Code <span class="sf-req">*</span></label>
                            <input type="text"
                                   id="code"
                                   name="code"
                                   inputmode="numeric"
                                   pattern="[0-9]*"
                                   maxlength="6"
                                   autocomplete="one-time-code"
                                   autofocus
                                   placeholder="6-digit code"
                                   class="form-control sf-otp @error('code') is-invalid @enderror">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="sf-expiry">
                            <i class="fa fa-clock-o" aria-hidden="true"></i>
                            <span>The code stops working {{ \App\Support\Otp::EXPIRES_MINUTES }} minutes after it was sent.</span>
                        </div>

                        <div class="form-group mb-3">
                            <label for="password">New Password <span class="sf-req">*</span></label>
                            <div class="sf-password">
                                <input type="password"
                                       id="password"
                                       name="password"
                                       placeholder="At least 6 characters"
                                       autocomplete="new-password"
                                       class="form-control @error('password') is-invalid @enderror">
                                <button type="button" class="sf-password-eye" data-toggle-password="#password"
                                        aria-label="Show password">
                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label for="password_confirmation">Confirm New Password <span class="sf-req">*</span></label>
                            <div class="sf-password">
                                <input type="password"
                                       id="password_confirmation"
                                       name="password_confirmation"
                                       placeholder="Re-enter the new password"
                                       autocomplete="new-password"
                                       class="form-control">
                                <button type="button" class="sf-password-eye" data-toggle-password="#password_confirmation"
                                        aria-label="Show password">
                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning">Reset Password</button>
                    </form>

                    <div class="sf-help-box">
                        <p>
                            <strong>Code not arriving?</strong>
                            An administrator can generate a new password by hand and email it straight to you.
                        </p>
                        <a class="btn btn-warning"
                           href="{{ route('account.help', ['type' => 'password', 'email' => $email]) }}">
                            Ask an admin instead
                        </a>
                    </div>

                    <div class="sf-auth-foot">
                        <a href="{{ route('password.request') }}">Send the code again, or use another email</a>
                        <div class="mt-2">
                            <a href="{{ route('login') }}" class="sf-link-muted">Back to login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
    (function () {
        var code = document.getElementById('code');

        if (code) {
            code.addEventListener('input', function () {
                var cleaned = code.value.replace(/\D/g, '').slice(0, 6);

                if (cleaned !== code.value) {
                    code.value = cleaned;
                }
            });
        }

        document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var field = document.querySelector(btn.getAttribute('data-toggle-password'));

                if (!field) {
                    return;
                }

                var showing = field.type === 'text';
                field.type = showing ? 'password' : 'text';
                btn.querySelector('i').className = showing ? 'fa fa-eye' : 'fa fa-eye-slash';
                btn.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
            });
        });
    })();
</script>
@endpush
