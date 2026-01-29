<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="" />
    <meta name="keyword" content="" />
    <meta name="author" content="flexilecode" />

    <title>Duralux || Dashboard</title>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/favicon.ico') }}" />

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendors.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/daterangepicker.min.css') }}" />

    <!-- Theme CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/theme.min.css') }}" />
</head>

<body>

    <!-- ================= Sidebar ================= -->
    @include('backend.partials.sidebar')
    <!-- ========================================== -->

    <!-- ================= Header ================= -->
    @include('backend.partials.header')
    <!-- ========================================== -->

    <!-- ================= Main Content ================= -->
    <main class="nxl-container">
        <div class="nxl-content">

            {{-- Page specific content --}}
            @yield('content')

        </div>
    </main>
    <!-- =============================================== -->

    <!-- ================= Theme Customizer ================= -->
    @include('backend.partials.customizer')
    <!-- =================================================== -->

    <!-- ================= Scripts ================= -->

    <!-- Vendors JS -->
    <script src="{{ asset('assets/vendors/js/vendors.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/js/daterangepicker.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/js/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/js/circle-progress.min.js') }}"></script>

    <!-- App Init -->
    <script src="{{ asset('assets/js/common-init.min.js') }}"></script>
    <script src="{{ asset('assets/js/dashboard-init.min.js') }}"></script>

    <!-- Theme Customizer -->
    <script src="{{ asset('assets/js/theme-customizer-init.min.js') }}"></script>

    <!-- ================= SweetAlert2 ================= -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- ================= Global SweetAlert Messages ================= -->
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: "{{ session('success') }}",
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: "{{ session('error') }}"
            });
        </script>
    @endif

    <!-- ================= SweetAlert Confirmations ================= -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // 🔴 DELETE CONFIRM (any form with .delete-form)
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This action cannot be undone!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            // 🔒 LOGOUT CONFIRM (sidebar logout)
            document.querySelectorAll('.logout-form').forEach(form => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    Swal.fire({
                        title: 'Logout?',
                        text: 'You will be logged out from admin panel',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Logout',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

        });
    </script>

</body>
</html>
