<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <meta name="description" content="Order fresh food online — fast delivery, great prices." />
  <meta name="author" content="" />

  <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

  <title>@yield('title', 'Feane') · Feane</title>

  <!-- bootstrap core css -->
  <link rel="stylesheet" href="{{ asset('feane-1.0.0/css/bootstrap.css') }}">

  <!-- owl slider -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">

  <!-- nice select -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/css/nice-select.min.css">

  <!-- font awesome -->
  <link rel="stylesheet" href="{{ asset('feane-1.0.0/css/font-awesome.min.css') }}">

  <!-- custom css -->
  <link rel="stylesheet" href="{{ asset('feane-1.0.0/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('feane-1.0.0/css/responsive.css') }}">

  <!-- Design refresh (loaded last so it wins over the template css) -->
  <link rel="stylesheet" href="{{ asset('assets/css/storefront-refresh.css') }}">

  @stack('styles')
</head>

<body>

  <div class="hero_area">

    <div class="bg-box">
      <img src="{{ asset('images/hero-bg.jpg') }}" alt="">
    </div>

    <!-- header -->
    @include('frontend.partials.header')

    <!-- page content -->
    @yield('content')

    <!-- footer -->
    @include('frontend.partials.footer')

  </div>{{-- /.hero_area — the template shipped this unclosed --}}

  <!-- jquery -->
  <script src="{{ asset('feane-1.0.0/js/jquery-3.4.1.min.js') }}"></script>

  <!-- popper -->
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>

  <!-- bootstrap js -->
  <script src="{{ asset('feane-1.0.0/js/bootstrap.js') }}"></script>

  <!-- owl slider -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>

  <!-- isotope -->
  <script src="https://unpkg.com/isotope-layout@3.0.4/dist/isotope.pkgd.min.js"></script>

  <!-- nice select -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/js/jquery.nice-select.min.js"></script>

  <!-- custom js -->
  <script src="{{ asset('feane-1.0.0/js/custom.js') }}"></script>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  {{-- Flash messages --}}
  @if (session('success'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: @json(session('success')),
        confirmButtonColor: '#f1b816'
      });
    </script>
  @endif

  @if (session('error'))
    <script>
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: @json(session('error')),
        confirmButtonColor: '#d33'
      });
    </script>
  @endif

  @if (session('info'))
    <script>
      Swal.fire({
        icon: 'info',
        title: 'Notice',
        text: @json(session('info')),
        confirmButtonColor: '#f1b816'
      });
    </script>
  @endif

  <script src="{{ asset('assets/js/storefront-refresh.js') }}"></script>

  @stack('scripts')

</body>

</html>
