
<!DOCTYPE html>
<html>

<head>
  <!-- Basic -->
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <!-- Mobile Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <!-- Site Metas -->
  <meta name="keywords" content="" />
  <meta name="description" content="" />
  <meta name="author" content="" />
<link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">


  <title> Feane </title>

  <!-- bootstrap core css -->
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


</head>

<body>

  <div class="hero_area">
    <div class="bg-box">
      <img src="images/hero-bg.jpg" alt="">
    </div>
    <!-- header section strats -->
   @include('frontend.partials.header')
    <!-- end header section -->
@yield('content')

  <!-- end client section -->

  <!-- footer section -->
@include('frontend.partials.footer')
  <!-- footer section -->

  <!-- jQery -->
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
  <!-- End Google Map -->

</body>

</html>