@extends('frontend.master')
@section('title', 'Login')

@section('content')

<section class="sf-auth-wrap">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">

                <div class="sf-auth-card">
                    <h3>Welcome back</h3>
                    <p class="sf-auth-sub">Sign in to order, track deliveries and keep your favourites.</p>

                    <form action="{{ route('login.submit') }}" method="POST">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="login">Username or Email</label>
                            <input type="text"
                                   id="login"
                                   name="login"
                                   value="{{ old('login') }}"
                                   autocomplete="username"
                                   class="form-control @error('login') is-invalid @enderror"
                                   placeholder="Username or Email">
                            @error('login')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="password">Password</label>
                            <div class="sf-password">
                                <input type="password"
                                       id="password"
                                       name="password"
                                       autocomplete="current-password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="Your password">
                                <button type="button" class="sf-password-eye" data-toggle-password="#password"
                                        aria-label="Show password">
                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                       {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>

                            <a href="{{ route('password.request') }}" class="sf-link-accent">Forgot password?</a>
                        </div>

                        <button type="submit" class="btn btn-warning">Sign In</button>
                    </form>

                    {{--
                        Locked out? Two escape hatches, surfaced only when the
                        controller says they apply, so the form does not shout
                        at people whose sign-in simply worked.
                    --}}
                    @if (session('offer_activation_help'))
                        <div class="sf-help-box">
                            <p>
                                <strong>This account is inactive.</strong>
                                An administrator has switched it off, so signing in is blocked.
                                Send them a request and they will reactivate it and email you.
                            </p>
                            <a class="btn btn-warning"
                               href="{{ route('account.help', ['type' => 'activation', 'email' => session('help_email')]) }}">
                                Request reactivation
                            </a>
                        </div>
                    @elseif (session('offer_password_help'))
                        <div class="sf-help-box">
                            <p>
                                <strong>Cannot get in?</strong>
                                If the reset code never reaches you, ask an administrator to issue a new
                                password. They will email it to you directly.
                            </p>
                            <a class="btn btn-warning"
                               href="{{ route('account.help', ['type' => 'password', 'email' => old('login')]) }}">
                                Ask an admin for a new password
                            </a>
                        </div>
                    @endif

                    <div class="sf-auth-foot">
                        New here? <a href="{{ route('register') }}">Create an account</a>
                        <div class="mt-2">
                            <a href="{{ route('account.help', 'activation') }}" class="sf-link-muted">
                                Account deactivated?
                            </a>
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
</script>
@endpush
