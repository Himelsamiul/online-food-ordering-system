@extends('frontend.master')
@section('title', 'Create an Account')

@push('styles')
<style>
    .sf-signup-card { max-width: 640px; }

    /* ---------- two-step indicator ---------- */
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

    .sf-step.is-current { color: var(--sf-ink); }

    .sf-step.is-current .sf-step-no {
        background: linear-gradient(135deg, var(--sf-accent), var(--sf-accent-dark));
        color: rgba(0, 0, 0, .82);
        border-color: transparent;
    }

    .sf-step-line {
        width: 36px;
        height: 1px;
        background: var(--sf-glass-line);
    }

    .sf-fieldset { margin-bottom: 24px; }

    .sf-fieldset-title {
        display: block;
        color: var(--sf-accent);
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: .09em;
        text-transform: uppercase;
        margin: 0 0 16px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--sf-glass-line);
    }

    @media (max-width: 575.98px) {
        .sf-step span:not(.sf-step-no) { display: none; }
        .sf-step-line { width: 24px; }
    }
</style>
@endpush

@section('content')

<section class="registration-section sf-auth-wrap">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-md-9">

                <div class="sf-auth-card sf-signup-card">

                    <div class="sf-steps">
                        <span class="sf-step is-current">
                            <span class="sf-step-no">1</span>
                            <span>Your details</span>
                        </span>
                        <span class="sf-step-line"></span>
                        <span class="sf-step">
                            <span class="sf-step-no">2</span>
                            <span>Verify email</span>
                        </span>
                    </div>

                    <h3>Create your account</h3>
                    <p class="sf-auth-sub">
                        Fill this in and we will email you a 6-digit code. Your account is created
                        the moment you enter it.
                    </p>

                    <form action="{{ route('register.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- ============ ABOUT YOU ============ --}}
                        <div class="sf-fieldset">
                            <span class="sf-fieldset-title">About you</span>

                            <div class="form-row">
                                <div class="form-group col-md-7">
                                    <label for="full_name">Full Name <span class="sf-req">*</span></label>
                                    <input type="text"
                                           id="full_name"
                                           name="full_name"
                                           value="{{ old('full_name') }}"
                                           placeholder="e.g. John Doe"
                                           autocomplete="name"
                                           class="form-control @error('full_name') is-invalid @enderror">
                                    @error('full_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-5">
                                    <label for="dob">Date of Birth <span class="sf-req">*</span></label>
                                    <input type="date"
                                           id="dob"
                                           name="dob"
                                           value="{{ old('dob') }}"
                                           placeholder="YYYY-MM-DD"
                                           class="form-control @error('dob') is-invalid @enderror">
                                    @error('dob')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <label for="username">Username <span class="sf-req">*</span></label>
                                <input type="text"
                                       id="username"
                                       name="username"
                                       value="{{ old('username') }}"
                                       placeholder="e.g. johndoe123"
                                       autocomplete="username"
                                       class="form-control @error('username') is-invalid @enderror">
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="sf-hint">You can sign in with either your username or your email.</small>
                            </div>
                        </div>

                        {{-- ============ CONTACT ============ --}}
                        <div class="sf-fieldset">
                            <span class="sf-fieldset-title">Contact &amp; delivery</span>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="phone">Phone Number <span class="sf-req">*</span></label>
                                    <input type="text"
                                           id="phone"
                                           name="phone"
                                           value="{{ old('phone') }}"
                                           placeholder="01XXXXXXXXX"
                                           inputmode="numeric"
                                           autocomplete="tel"
                                           class="form-control @error('phone') is-invalid @enderror">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="sf-hint">11 digits, starting 013 to 019.</small>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="email">Email Address <span class="sf-req">*</span></label>
                                    <input type="email"
                                           id="email"
                                           name="email"
                                           value="{{ old('email') }}"
                                           placeholder="example@mail.com"
                                           autocomplete="email"
                                           class="form-control @error('email') is-invalid @enderror">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="sf-hint">The verification code goes here.</small>
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <label for="address">Address <span class="sf-optional">(optional)</span></label>
                                <input type="text"
                                       id="address"
                                       name="address"
                                       value="{{ old('address') }}"
                                       placeholder="e.g. 123 Main Street, City"
                                       autocomplete="street-address"
                                       class="form-control @error('address') is-invalid @enderror">
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="sf-hint">Add it now and checkout will fill it in for you.</small>
                            </div>
                        </div>

                        {{-- ============ SECURITY ============ --}}
                        <div class="sf-fieldset">
                            <span class="sf-fieldset-title">Password</span>

                            <div class="form-group">
                                <label for="password">Password <span class="sf-req">*</span></label>
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

                            <div class="form-group mb-0">
                                <label for="password_confirmation">Confirm Password <span class="sf-req">*</span></label>
                                <div class="sf-password">
                                    <input type="password"
                                           id="password_confirmation"
                                           name="password_confirmation"
                                           placeholder="Re-enter your password"
                                           autocomplete="new-password"
                                           class="form-control">
                                    <button type="button" class="sf-password-eye" data-toggle-password="#password_confirmation"
                                            aria-label="Show password">
                                        <i class="fa fa-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- ============ PHOTO ============ --}}
                        <div class="sf-fieldset">
                            <span class="sf-fieldset-title">Profile picture <span class="sf-optional">(optional)</span></span>

                            <div class="form-group mb-0">
                                <label for="image" class="sr-only">Profile Image</label>
                                <input type="file"
                                       id="image"
                                       name="image"
                                       accept="image/jpeg,image/png"
                                       class="form-control @error('image') is-invalid @enderror">
                                @error('image')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="sf-hint">JPG or PNG, up to 2 MB. You can add one later from your profile.</small>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning">Send Verification Code</button>
                    </form>

                    <div class="sf-auth-foot">
                        Already have an account? <a href="{{ route('login') }}">Sign in</a>
                        <div class="mt-2">
                            <a href="{{ route('account.help', 'activation') }}" class="sf-link-muted">
                                Had an account that stopped working?
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
