@php
    /**
     * Shell for every standalone admin auth page.
     *
     * These pages cannot extend backend.master — that layout renders the
     * sidebar and header, both of which need an authenticated user. So the
     * document is assembled here instead, with the same stylesheets and the
     * same pre-paint theme snippet so dark mode does not flash on the way in.
     *
     * Usage: @include('backend.auth.partials.shell', ['title' => '...'])  is NOT
     * how this works — it is a layout. Extend it:
     *   @extends('backend.auth.partials.shell')
     *   @section('title', 'Forgot password')
     *   @section('card') ... @endsection
     */
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="robots" content="noindex, nofollow" />

    <title>@yield('title', 'Admin') · Admin Panel</title>

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/favicon.ico') }}" />

    <script>
        (function () {
            try {
                if (localStorage.getItem('app-skin') === 'app-skin-dark') {
                    document.documentElement.classList.add('app-skin-dark');
                }
            } catch (e) {}
        })();
    </script>

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendors.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/theme.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/admin-refresh.css') }}" />

    @stack('styles')
</head>

<body>

    <div class="auth-shell">

        <button type="button" class="theme-toggle auth-theme-toggle" data-theme-toggle
                aria-pressed="false" title="Switch to dark mode">
            <i class="feather-moon icon-moon"></i>
            <i class="feather-sun icon-sun"></i>
        </button>

        <div class="auth-card @yield('card-class')">

            <div class="auth-brand">
                <span class="brand-mark"><i class="feather-hexagon"></i></span>
                <span class="brand-name">Feane<span style="color:var(--ar-primary)">Admin</span></span>
            </div>

            @if (session('success'))
                <div class="alert alert-success">
                    <i class="feather-check-circle me-1"></i> {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    <i class="feather-alert-circle me-1"></i> {{ session('error') }}
                </div>
            @endif

            @if (session('info'))
                <div class="alert alert-info">
                    <i class="feather-info me-1"></i> {{ session('info') }}
                </div>
            @endif

            @if ($errors->any() && !$errors->has('login'))
                <div class="alert alert-danger">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('card')

        </div>

        <p class="auth-foot-link">
            <a href="{{ route('home') }}"><i class="feather-arrow-left"></i> Back to the storefront</a>
        </p>
    </div>

    <script src="{{ asset('assets/js/admin-refresh.js') }}"></script>
    @stack('scripts')
</body>
</html>
