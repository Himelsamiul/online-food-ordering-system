<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f172a">

    <title>@yield('title', 'Rider') · Feane</title>

    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('feane-1.0.0/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/rider.css') }}">
</head>
<body class="rider-body">

    @auth('rider')
        <header class="rider-header">
            <div class="rider-header-inner">
                <div class="rider-brand">
                    <span class="rider-mark"><i class="fa fa-motorcycle" aria-hidden="true"></i></span>
                    <div>
                        <strong>{{ auth('rider')->user()->name }}</strong>
                        <small>Rider</small>
                    </div>
                </div>

                <form method="POST" action="{{ route('rider.logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="rider-logout" aria-label="Sign out">
                        <i class="fa fa-sign-out" aria-hidden="true"></i>
                    </button>
                </form>
            </div>
        </header>
    @endauth

    <main class="rider-main">
        @if (session('success'))
            <div class="rider-flash is-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="rider-flash is-error">{{ session('error') }}</div>
        @endif
        @if (session('info'))
            <div class="rider-flash is-info">{{ session('info') }}</div>
        @endif

        @yield('content')
    </main>

    @auth('rider')
        {{-- Thumb-reach navigation: riders use this one-handed, standing up. --}}
        <nav class="rider-tabs">
            <a href="{{ route('rider.dashboard') }}"
               class="{{ request()->routeIs('rider.dashboard') ? 'active' : '' }}">
                <i class="fa fa-list-ul" aria-hidden="true"></i>
                <span>My drops</span>
            </a>
            <a href="{{ route('rider.history') }}"
               class="{{ request()->routeIs('rider.history') ? 'active' : '' }}">
                <i class="fa fa-check-square-o" aria-hidden="true"></i>
                <span>Completed</span>
            </a>
        </nav>
    @endauth

    @stack('scripts')
</body>
</html>
