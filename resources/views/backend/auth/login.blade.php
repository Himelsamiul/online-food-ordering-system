<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="description" content="Admin control panel sign in" />
    <meta name="robots" content="noindex, nofollow" />

    <title>Sign in · Admin Panel</title>

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/favicon.ico') }}" />

    {{--
        Apply the saved theme before the first paint, exactly as the admin
        layout does. Without it a dark-mode user gets a white flash here.
    --}}
    <script>
        (function () {
            try {
                if (localStorage.getItem('app-skin') === 'app-skin-dark') {
                    document.documentElement.classList.add('app-skin-dark');
                }
            } catch (e) {}
        })();
    </script>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}" />

    <!-- Vendors CSS (Feather icon font lives here) -->
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendors.min.css') }}" />

    <!-- Theme CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/theme.min.css') }}" />

    <!-- Design system (loaded last so it wins over the vendor theme) -->
    <link rel="stylesheet" href="{{ asset('assets/css/admin-refresh.css') }}" />

    {{--
        Page-only styles. This view is standalone, so the design-system rules
        that are scoped to `.nxl-content` (alerts, checkboxes) do not reach it
        and are re-declared here — always through the --ar-* tokens so light
        and dark stay in step.
    --}}
    <style>
        .auth-topbar {
            position: fixed;
            top: 18px;
            right: 18px;
            z-index: 5;
        }

        .auth-card .form-label {
            display: block;
        }

        .auth-card .form-control::placeholder {
            color: var(--ar-faint);
        }

        .field {
            margin-bottom: 16px;
        }

        /* The design-system button base is scoped to .nxl-content, so the
           icon + label alignment is set here. */
        .auth-card .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 14.5px;
            transition: box-shadow .2s var(--ar-ease), transform .14s var(--ar-ease);
        }

        .auth-card .btn-primary:focus-visible {
            outline: none;
            box-shadow: 0 0 0 3.5px var(--ar-primary-soft), 0 10px 26px var(--ar-primary-glow);
        }

        .auth-card .btn-primary:active {
            transform: translateY(1px);
        }

        .field-hint {
            display: block;
            margin-top: 6px;
            font-size: 12.5px;
            color: var(--ar-danger);
        }

        /* Password field + eye toggle */
        .pw-wrap {
            position: relative;
        }

        .pw-wrap .form-control {
            padding-right: 44px;
        }

        .pw-toggle {
            position: absolute;
            top: 50%;
            right: 6px;
            transform: translateY(-50%);
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            background: transparent;
            color: var(--ar-muted);
            border-radius: var(--ar-radius-xs);
            cursor: pointer;
            transition: background-color .18s, color .18s;
        }

        .pw-toggle:hover,
        .pw-toggle:focus-visible {
            background: var(--ar-primary-soft);
            color: var(--ar-primary);
            outline: none;
        }

        .pw-toggle i {
            font-size: 15px;
        }

        /* Remember me */
        .auth-card .form-check-input {
            border-color: var(--ar-line);
            background-color: var(--ar-surface);
            cursor: pointer;
        }

        .auth-card .form-check-input:checked {
            background-color: var(--ar-primary);
            border-color: var(--ar-primary);
        }

        .auth-card .form-check-input:focus {
            border-color: var(--ar-primary);
            box-shadow: 0 0 0 3px var(--ar-primary-soft);
        }

        .auth-card .form-check-label {
            color: var(--ar-ink-2);
            font-size: 13.5px;
            cursor: pointer;
        }

        /* Alerts — the design-system versions are scoped to .nxl-content */
        .auth-card .alert {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            border: 1px solid transparent;
            border-radius: var(--ar-radius-sm);
            padding: 11px 14px;
            font-size: 13.5px;
            font-weight: 550;
            margin-bottom: 18px;
        }

        .auth-card .alert i {
            font-size: 15px;
            line-height: 1.35;
        }

        .auth-card .alert-danger {
            background: var(--ar-danger-soft);
            border-color: var(--ar-danger);
            color: var(--ar-danger);
        }

        .auth-card .alert-success {
            background: var(--ar-success-soft);
            border-color: var(--ar-success);
            color: var(--ar-success);
        }

        /* Footer bits */
        .auth-note {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin: 18px 0 0;
            font-size: 12.5px;
            color: var(--ar-muted);
        }

        .auth-foot {
            margin: 20px 0 0;
            padding-top: 18px;
            border-top: 1px solid var(--ar-line-soft);
            text-align: center;
        }

        .auth-foot a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 600;
            color: var(--ar-muted);
            text-decoration: none;
            transition: color .18s;
        }

        .auth-foot a:hover {
            color: var(--ar-primary);
        }

        @media (max-width: 575.98px) {
            .auth-card {
                padding: 26px 20px;
            }

            .auth-topbar {
                top: 12px;
                right: 12px;
            }
        }
    </style>
</head>

<body>

    <div class="auth-shell">

        {{-- Dark / light, same control the header uses --}}
        <div class="auth-topbar">
            <button type="button" class="theme-toggle" data-theme-toggle
                    aria-pressed="false" title="Switch to dark mode">
                <i class="feather-moon icon-moon"></i>
                <i class="feather-sun icon-sun"></i>
            </button>
        </div>

        <div class="auth-card">

            <div class="auth-brand">
                <span class="brand-mark"><i class="feather-coffee"></i></span>
                <span class="brand-name">Feane Admin</span>
            </div>

            <h4>Welcome back</h4>
            <p class="auth-sub">Sign in to the control panel</p>

            @if (session('success'))
                <div class="alert alert-success" role="status">
                    <i class="feather-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->has('login'))
                <div class="alert alert-danger" role="alert">
                    <i class="feather-alert-circle"></i>
                    <span>{{ $errors->first('login') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}" novalidate>
                @csrf

                <div class="field">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email"
                           name="email"
                           id="email"
                           class="form-control"
                           value="{{ old('email') }}"
                           placeholder="you@example.com"
                           autocomplete="email"
                           autofocus
                           required>
                    @error('email')
                        <span class="field-hint">{{ $message }}</span>
                    @enderror
                </div>

                <div class="field">
                    <label class="form-label" for="password">Password</label>
                    <div class="pw-wrap">
                        <input type="password"
                               name="password"
                               id="password"
                               class="form-control"
                               placeholder="Enter your password"
                               autocomplete="current-password"
                               required>
                        <button type="button" class="pw-toggle" id="pwToggle"
                                aria-controls="password" aria-pressed="false"
                                aria-label="Show password" title="Show password">
                            <i class="feather-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <span class="field-hint">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember"
                           value="1" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">Keep me signed in</label>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="feather-log-in"></i> Sign in
                </button>
            </form>

            <p class="auth-note">
                <i class="feather-shield"></i>
                <span>Every sign-in attempt is recorded.</span>
            </p>

            <p class="auth-foot">
                <a href="{{ route('home') }}">
                    <i class="feather-arrow-left"></i> Back to the storefront
                </a>
            </p>
        </div>
    </div>

    <script>
        (function () {
            var toggle = document.getElementById('pwToggle');
            var input  = document.getElementById('password');

            if (!toggle || !input) {
                return;
            }

            toggle.addEventListener('click', function () {
                var reveal = input.type === 'password';

                input.type = reveal ? 'text' : 'password';
                toggle.setAttribute('aria-pressed', reveal ? 'true' : 'false');
                toggle.setAttribute('aria-label', reveal ? 'Hide password' : 'Show password');
                toggle.setAttribute('title', reveal ? 'Hide password' : 'Show password');
                toggle.querySelector('i').className = reveal ? 'feather-eye-off' : 'feather-eye';

                input.focus();
            });
        })();
    </script>

    <script src="{{ asset('assets/js/admin-refresh.js') }}"></script>

</body>
</html>
