<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="description" content="Admin control panel" />

    <title>@yield('title', 'Dashboard') · Admin Panel</title>

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/favicon.ico') }}" />

    {{--
        Apply the saved theme before the first paint. Doing this in the body
        or in a deferred script gives a white flash on every navigation for
        anyone using dark mode.
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

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendors.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/daterangepicker.min.css') }}" />

    <!-- Theme CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/theme.min.css') }}" />

    <!-- Design system (loaded last so it wins over the vendor theme) -->
    <link rel="stylesheet" href="{{ asset('assets/css/admin-refresh.css') }}" />

    @stack('styles')
</head>

<body>

    @include('backend.partials.sidebar')
    @include('backend.partials.header')

    <main class="nxl-container">
        <div class="nxl-content">
            @yield('content')
        </div>
    </main>

    <!-- ================= Scripts ================= -->
    <script src="{{ asset('assets/vendors/js/vendors.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/js/daterangepicker.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/js/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/js/circle-progress.min.js') }}"></script>

    <script src="{{ asset('assets/js/common-init.min.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Flash messages --}}
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: @json(session('success')),
                timer: 2600,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Something went wrong',
                text: @json(session('error'))
            });
        </script>
    @endif

    @if (session('info'))
        <script>
            Swal.fire({
                icon: 'info',
                title: 'Notice',
                text: @json(session('info')),
                timer: 3500,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Please check the form',
                html: @json('<ul style="text-align:left;margin:0;padding-left:18px">' .
                    collect($errors->all())->map(fn ($e) => '<li>' . e($e) . '</li>')->implode('') .
                    '</ul>')
            });
        </script>
    @endif

    {{-- Confirmations --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // Any form with .delete-form asks first.
            document.querySelectorAll('.delete-form').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    if (form.dataset.confirmed) {
                        return;
                    }

                    e.preventDefault();

                    Swal.fire({
                        title: form.dataset.confirmTitle || 'Are you sure?',
                        text: form.dataset.confirmText || 'This action cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: form.dataset.confirmButton || 'Yes, delete it'
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            form.dataset.confirmed = '1';
                            form.submit();
                        }
                    });
                });
            });

            // Sidebar / header logout.
            document.querySelectorAll('.logout-form').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    if (form.dataset.confirmed) {
                        return;
                    }

                    e.preventDefault();

                    Swal.fire({
                        title: 'Log out?',
                        text: 'You will be signed out of the admin panel.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, log out',
                        cancelButtonText: 'Stay'
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            form.dataset.confirmed = '1';
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>

    <script src="{{ asset('assets/js/admin-refresh.js') }}"></script>

    @stack('scripts')

</body>
</html>
