@extends('frontend.master')
@section('title', 'Verify Your Email')

@push('styles')
<style>
    /* ---------- two-step indicator (matches the sign-up form) ---------- */
    .sf-steps {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .sf-step {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--sf-faint);
        font-size: 12.5px;
        font-weight: 600;
        white-space: nowrap;
    }

    .sf-step-no {
        width: 25px;
        height: 25px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        font-size: 12px;
        font-weight: 800;
        background: rgba(255, 255, 255, .08);
        color: var(--sf-muted);
        border: 1px solid var(--sf-glass-line);
    }

    .sf-step.is-done .sf-step-no {
        background: rgba(29, 191, 115, .18);
        color: var(--sf-green);
        border-color: rgba(29, 191, 115, .45);
    }

    .sf-step.is-current { color: var(--sf-ink); }

    .sf-step.is-current .sf-step-no {
        background: linear-gradient(135deg, var(--sf-accent), var(--sf-accent-dark));
        color: rgba(0, 0, 0, .82);
        border-color: transparent;
    }

    .sf-step-line { width: 36px; height: 1px; background: var(--sf-glass-line); }

    /* ---------- the code field ---------- */
    .sf-otp {
        font-family: 'Courier New', Courier, monospace;
        font-size: 30px;
        font-weight: 700;
        letter-spacing: 14px;
        text-indent: 14px; /* balances the trailing letter-space so it looks centred */
        text-align: center;
        padding: 14px 12px;
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
        margin: 14px 0 22px;
        padding: 10px 14px;
        border-radius: var(--sf-radius-sm);
        background: rgba(241, 184, 22, .1);
        border: 1px solid rgba(241, 184, 22, .3);
        color: rgba(255, 255, 255, .84);
        font-size: 13px;
    }

    .sf-expiry i { color: var(--sf-accent); }

    .sf-resend {
        margin-top: 18px;
        padding-top: 16px;
        border-top: 1px solid var(--sf-glass-line);
        text-align: center;
    }

    .sf-resend p { color: var(--sf-muted); font-size: 13.5px; margin: 0 0 10px; }

    /* The card forces .btn to full width — the resend button is secondary. */
    .sf-auth-card .sf-resend .btn { width: auto; padding: 8px 20px; font-size: 13.5px; }
</style>
@endpush

@section('content')

<section class="registration-section sf-auth-wrap">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">

                <div class="sf-auth-card">

                    <div class="sf-steps">
                        <span class="sf-step is-done">
                            <span class="sf-step-no"><i class="fa fa-check" aria-hidden="true"></i></span>
                            <span>Your details</span>
                        </span>
                        <span class="sf-step-line"></span>
                        <span class="sf-step is-current">
                            <span class="sf-step-no">2</span>
                            <span>Verify email</span>
                        </span>
                    </div>

                    <div class="sf-request-badge">
                        <i class="fa fa-envelope-o" aria-hidden="true"></i>
                    </div>

                    <h3>Check your inbox</h3>
                    <p class="sf-auth-sub">
                        We sent a 6-digit code to <strong>{{ $email }}</strong>.
                        Enter it below to finish creating your account.
                    </p>

                    <form action="{{ route('register.verify.submit') }}" method="POST">
                        @csrf

                        <div class="form-group mb-2">
                            <label for="code" class="sr-only">Verification Code</label>
                            <input type="text"
                                   id="code"
                                   name="code"
                                   inputmode="numeric"
                                   pattern="[0-9]*"
                                   maxlength="6"
                                   autocomplete="one-time-code"
                                   autofocus
                                   placeholder="Enter your 6-digit code"
                                   class="form-control sf-otp @error('code') is-invalid @enderror">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="sf-expiry">
                            <i class="fa fa-clock-o" aria-hidden="true"></i>
                            <span>The code stops working {{ \App\Support\Otp::EXPIRES_MINUTES }} minutes after it was sent.</span>
                        </div>

                        <button type="submit" class="btn btn-warning">Verify &amp; Create Account</button>
                    </form>

                    <div class="sf-resend">
                        <p>Nothing in your inbox? Check the spam folder, then ask for a new code.</p>

                        <form action="{{ route('register.resend') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-light">Resend the code</button>
                        </form>
                    </div>

                    <div class="sf-auth-foot">
                        Typed the wrong email? <a href="{{ route('register') }}">Start again</a>
                        <div class="mt-2">
                            <a href="{{ route('account.help', 'activation') }}" class="sf-link-muted">
                                Codes never arrive? Ask an admin for help
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
    (function () {
        var code = document.getElementById('code');

        if (!code) {
            return;
        }

        // Keeps pasted codes usable: strips spaces, dashes and stray letters.
        code.addEventListener('input', function () {
            var cleaned = code.value.replace(/\D/g, '').slice(0, 6);

            if (cleaned !== code.value) {
                code.value = cleaned;
            }
        });
    })();
</script>
@endpush
