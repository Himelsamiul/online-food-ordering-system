@extends('frontend.master')

@section('title', 'Home')

@push('styles')
@include('frontend.partials.food-card-styles')
@include('frontend.partials.category-card-styles')
<style>
    /* =====================================================================
       HERO
       .hero_area is a 100vh flex column and the template closes it right
       after the slider, so .slider_section already fills the first screen.
       Everything here is type, spacing and colour on top of that.
       ===================================================================== */
    .slider_section { padding: 20px 0 50px; }

    .slider_section .detail-box { margin-bottom: 42px; }

    .hero-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 20px;
        padding: 7px 16px;
        border-radius: 30px;
        background: rgba(255, 255, 255, .1);
        border: 1px solid var(--sf-glass-line);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        color: var(--sf-accent);
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .slider_section .detail-box h1 {
        font-size: clamp(2.9rem, 7vw, 4.9rem);
        line-height: 1.04;
        font-weight: 700;
        letter-spacing: 0;
        margin-bottom: 16px;
        color: var(--sf-ink);
        text-shadow: 0 10px 36px rgba(0, 0, 0, .6);
    }

    .slider_section .detail-box p {
        max-width: 540px;
        margin-bottom: 26px;
        font-size: 16.5px;
        line-height: 1.7;
        color: rgba(255, 255, 255, .82);
    }

    .slider_section .btn-box {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    /* Specific enough to beat `.slider_section .detail-box a` in the
       template stylesheet, which paints every hero link amber. */
    .slider_section .detail-box .hero-btn,
    .slider_section .detail-box .hero-btn-ghost {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-top: 0;
        padding: 14px 30px;
        border-radius: 30px;
        font-size: 15px;
        font-weight: 700;
        text-decoration: none;
        transition: transform .18s, box-shadow .18s, background-color .18s, color .18s;
    }

    .slider_section .detail-box .hero-btn {
        background: linear-gradient(135deg, var(--sf-accent), var(--sf-accent-dark));
        color: rgba(0, 0, 0, .85);
        border: none;
        box-shadow: 0 14px 32px rgba(0, 0, 0, .5);
    }

    .slider_section .detail-box .hero-btn:hover {
        transform: translateY(-2px);
        color: rgba(0, 0, 0, .85);
    }

    .slider_section .detail-box .hero-btn-ghost {
        background: rgba(255, 255, 255, .1);
        border: 1px solid var(--sf-glass-line);
        color: var(--sf-ink);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    .slider_section .detail-box .hero-btn-ghost:hover {
        background: rgba(255, 255, 255, .18);
        color: var(--sf-ink);
        transform: translateY(-2px);
    }

    .slider_section .carousel-indicators {
        position: static;
        margin: 0;
        justify-content: flex-start;
    }

    .slider_section .carousel-indicators li {
        width: 10px;
        height: 10px;
        margin: 0 7px 0 0;
        border: none;
        border-radius: 20px;
        background: rgba(255, 255, 255, .4);
        opacity: 1;
        transition: width .25s ease, background-color .25s ease;
    }

    .slider_section .carousel-indicators li.active {
        width: 32px;
        height: 10px;
        background: var(--sf-accent);
    }

    .hero-trust {
        display: flex;
        flex-wrap: wrap;
        gap: 10px 28px;
        margin-top: 26px;
    }

    .hero-trust span {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        color: rgba(255, 255, 255, .72);
        font-size: 13.5px;
        font-weight: 600;
    }

    .hero-trust i { color: var(--sf-accent); }

    /* =====================================================================
       SECTIONS
       ===================================================================== */
    .home-section { padding-bottom: 78px; }
    .home-section-top { padding-top: 78px; }

    /* The header is sticky, so anchored jumps need clearance. */
    #offers, #categories, #highlights { scroll-margin-top: 96px; }

    @media (prefers-reduced-motion: no-preference) {
        html { scroll-behavior: smooth; }
    }

    .sf-page-head { margin-bottom: 28px; }

    .sf-page-head h2 {
        color: var(--sf-ink);
        font-weight: 700;
        font-size: 2.4rem;
        margin-bottom: 6px;
    }

    .sf-page-head p {
        color: var(--sf-muted);
        margin-bottom: 0;
    }

    .sf-eyebrow {
        display: inline-block;
        margin-bottom: 8px;
        color: var(--sf-accent);
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .view-menu-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 14px 40px;
        border-radius: 30px;
        background: linear-gradient(135deg, var(--sf-accent), var(--sf-accent-dark));
        color: rgba(0, 0, 0, .85);
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 14px 30px rgba(0, 0, 0, .45);
        transition: transform .18s, box-shadow .18s;
    }

    .view-menu-btn:hover,
    .view-menu-btn:focus {
        transform: translateY(-2px);
        color: rgba(0, 0, 0, .85);
        text-decoration: none;
    }

    /* ---------- empty highlight row ---------- */
    .home-empty {
        text-align: center;
        padding: 46px 22px;
        border-radius: var(--sf-radius);
        border: 1px dashed var(--sf-glass-line);
        background: var(--sf-glass);
    }

    .home-empty h5 { color: var(--sf-ink); font-weight: 700; margin-bottom: 6px; }
    .home-empty p { color: var(--sf-muted); margin-bottom: 0; }

    /* ---------- how it works ---------- */
    .hiw-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 18px;
    }

    .hiw-card { padding: 26px 24px; }

    .hiw-step {
        width: 46px;
        height: 46px;
        margin-bottom: 16px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        font-weight: 800;
        background: linear-gradient(135deg, var(--sf-accent), var(--sf-accent-dark));
        color: rgba(0, 0, 0, .85);
        box-shadow: 0 12px 26px rgba(0, 0, 0, .4);
    }

    .hiw-card h6 {
        color: var(--sf-ink);
        font-weight: 700;
        font-size: 16px;
        margin-bottom: 6px;
    }

    .hiw-card p {
        color: var(--sf-muted);
        font-size: 14px;
        line-height: 1.6;
        margin-bottom: 0;
    }

    /* ---------- about ----------
       The template paints this band a flat slate plate. The page already
       has a dark scrim behind everything, so the plate is redundant — the
       content sits in a glass panel instead. */
    .about_section {
        background: transparent;
        color: var(--sf-ink);
    }

    .about-panel { padding: 34px; }

    .about_section .img-box img {
        width: 100%;
        border-radius: var(--sf-radius);
    }

    .about_section .detail-box p {
        color: var(--sf-muted);
        line-height: 1.75;
        margin-bottom: 24px;
    }

    .about_section .detail-box .about-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-top: 0;
        padding: 12px 28px;
        border-radius: 30px;
        background: rgba(255, 255, 255, .1);
        border: 1px solid var(--sf-glass-line);
        color: var(--sf-ink);
        font-weight: 700;
        font-size: 14.5px;
        text-decoration: none;
        transition: background-color .18s, border-color .18s;
    }

    .about_section .detail-box .about-btn:hover {
        background: rgba(255, 255, 255, .18);
        border-color: var(--sf-accent);
        color: var(--sf-ink);
    }

    /* ---------- closing call to action ---------- */
    .cta-band {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 22px;
        padding: 34px 36px;
    }

    .cta-band h3 {
        color: var(--sf-ink);
        font-weight: 700;
        margin-bottom: 6px;
    }

    .cta-band p {
        color: var(--sf-muted);
        margin-bottom: 0;
        max-width: 520px;
    }

    .cta-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .cta-ghost {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 14px 30px;
        border-radius: 30px;
        background: rgba(255, 255, 255, .1);
        border: 1px solid var(--sf-glass-line);
        color: var(--sf-ink);
        font-weight: 700;
        text-decoration: none;
        transition: background-color .18s, border-color .18s;
    }

    .cta-ghost:hover {
        background: rgba(255, 255, 255, .18);
        border-color: var(--sf-accent);
        color: var(--sf-ink);
        text-decoration: none;
    }

    @media (max-width: 767.98px) {
        .home-section { padding-bottom: 56px; }
        .home-section-top { padding-top: 56px; }
        .sf-page-head h2 { font-size: 1.95rem; }
        .cta-band { padding: 26px 22px; }
        .about_section .img-box { margin-bottom: 26px; }
    }
</style>
@endpush

@section('content')

@php
    $hasPromos = ($promotions ?? collect())->count();

    $heroSlides = [
        [
            'eyebrow' => 'Cooked to order',
            'title'   => 'Fresh food, delivered hot',
            'text'    => 'Browse the whole menu on one page, add what you like to the cart, and pay by card or cash on delivery.',
        ],
        [
            'eyebrow' => 'Every offer we run',
            'title'   => 'Real codes, real savings',
            'text'    => 'Live coupons sit right on this page. Copy one, paste it in your cart, and watch the total come down.',
        ],
        [
            'eyebrow' => 'From our kitchen',
            'title'   => 'Know where your order is',
            'text'    => 'Every order carries a status you can follow from your profile, right up to the moment it reaches your door.',
        ],
    ];

    $secondCta = $hasPromos
        ? ['href' => '#offers',     'label' => "See today's offers"]
        : ['href' => '#categories', 'label' => 'Browse categories'];
@endphp

    <!-- slider section -->
    <section class="slider_section">
      <div id="customCarousel1" class="carousel slide" data-ride="carousel" data-interval="7000">

        <div class="carousel-inner">
          @foreach ($heroSlides as $i => $slide)
            <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
              <div class="container">
                <div class="row">
                  <div class="col-md-9 col-lg-7">
                    <div class="detail-box">
                      <span class="hero-eyebrow">
                        <i class="fa fa-circle" aria-hidden="true"></i> {{ $slide['eyebrow'] }}
                      </span>

                      <h1>{{ $slide['title'] }}</h1>

                      <p>{{ $slide['text'] }}</p>

                      <div class="btn-box">
                        <a href="{{ route('menu.index') }}" class="btn1 hero-btn">
                          Browse the menu <i class="fa fa-angle-right" aria-hidden="true"></i>
                        </a>
                        <a href="{{ $secondCta['href'] }}" class="hero-btn-ghost">
                          {{ $secondCta['label'] }}
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>

        <div class="container">
          <ol class="carousel-indicators">
            @foreach ($heroSlides as $i => $slide)
              <li data-target="#customCarousel1" data-slide-to="{{ $i }}"
                  class="{{ $i === 0 ? 'active' : '' }}"></li>
            @endforeach
          </ol>

          <div class="hero-trust">
            <span><i class="fa fa-credit-card" aria-hidden="true"></i> Card or cash on delivery</span>
            <span><i class="fa fa-ticket" aria-hidden="true"></i> Coupon codes at checkout</span>
            <span><i class="fa fa-truck" aria-hidden="true"></i> Order status you can follow</span>
          </div>
        </div>

      </div>
    </section>
    <!-- end slider section -->
  </div>{{-- the template closes .hero_area here; master's own closing tag is the stray one --}}

  {{-- ================= RUNNING OFFERS ================= --}}
  @if ($hasPromos)
    <section id="offers" class="offers-section home-section home-section-top">
      <div class="container">
        <div class="sf-page-head text-center">
          <span class="sf-eyebrow">Running offers</span>
          <h2>Save on this order</h2>
          <p>Tap a code to copy it, then paste it in your cart.</p>
        </div>

        @include('frontend.partials.promo-banners', ['promotions' => $promotions])
      </div>
    </section>
  @endif

  {{-- ================= BROWSE BY CATEGORY ================= --}}
  <section id="categories" class="category-section home-section {{ $hasPromos ? '' : 'home-section-top' }}">
    <div class="container">
      <div class="sf-page-head text-center">
        <span class="sf-eyebrow">Where to start</span>
        <h2>Browse by category</h2>
        <p>Pick a category to jump straight into the menu.</p>
      </div>

      @include('frontend.partials.category-cards', ['categories' => $categories])
    </div>
  </section>

  {{-- ================= HIGHLIGHTS ================= --}}
  <section id="highlights" class="highlights-section home-section">
    <div class="container">

      @if (($featuredFoods ?? collect())->count())
        <div class="sf-page-head text-center">
          <span class="sf-eyebrow">Hand-picked</span>
          <h2>Featured items</h2>
          <p>The dishes our kitchen is proudest of this week.</p>
        </div>

        <div class="row mb-5">
          @foreach ($featuredFoods as $food)
            @include('frontend.partials.food-card', ['food' => $food])
          @endforeach
        </div>
      @endif

      @if (($popularFoods ?? collect())->count())
        <div class="sf-page-head text-center">
          <span class="sf-eyebrow">Ordered again and again</span>
          <h2>Most popular</h2>
          <p>What everyone keeps coming back for.</p>
        </div>

        <div class="row mb-4">
          @foreach ($popularFoods as $food)
            @include('frontend.partials.food-card', ['food' => $food])
          @endforeach
        </div>
      @endif

      @if (!($featuredFoods ?? collect())->count() && !($popularFoods ?? collect())->count())
        <div class="home-empty">
          <h5>No highlights right now</h5>
          <p>The full menu is still open — have a look and order whatever you fancy.</p>
        </div>
      @endif

      <div class="text-center mt-5">
        <a href="{{ route('menu.index') }}" class="view-menu-btn">
          View the full menu <i class="fa fa-angle-right" aria-hidden="true"></i>
        </a>
      </div>

    </div>
  </section>

  {{-- ================= HOW ORDERING WORKS ================= --}}
  <section class="steps-section home-section">
    <div class="container">
      <div class="sf-page-head text-center">
        <span class="sf-eyebrow">Three steps</span>
        <h2>How ordering works</h2>
        <p>From the menu to your door, without a phone call.</p>
      </div>

      <div class="hiw-row">
        <div class="hiw-card glass-card">
          <div class="hiw-step">1</div>
          <h6>Pick your food</h6>
          <p>Filter the menu by category, price or keyword, then add the dishes you want to your cart.</p>
        </div>

        <div class="hiw-card glass-card">
          <div class="hiw-step">2</div>
          <h6>Apply a code and pay</h6>
          <p>Paste a coupon code in the cart if you have one, then check out by card or choose cash on delivery.</p>
        </div>

        <div class="hiw-card glass-card">
          <div class="hiw-step">3</div>
          <h6>Follow it to your door</h6>
          <p>Your profile shows the status of every order, from the kitchen to the rider on the way to you.</p>
        </div>
      </div>
    </div>
  </section>

  {{-- ================= ABOUT ================= --}}
  <section class="about_section home-section">
    <div class="container">
      <div class="about-panel glass-card">
        <div class="row align-items-center">
          <div class="col-md-6">
            <div class="img-box">
              <img src="{{ asset('images/about-img.png') }}" alt="Our kitchen">
            </div>
          </div>

          <div class="col-md-6">
            <div class="detail-box">
              <div class="sf-page-head text-left">
                <span class="sf-eyebrow">Who we are</span>
                <h2>We are Feane</h2>
              </div>

              <p>
                We keep the menu short on purpose. Everything is cooked to order from a small
                kitchen, packed the moment it comes off the heat, and sent out straight away —
                so what lands at your table is what just came out of the pan.
              </p>

              <a href="{{ route('about') }}" class="about-btn">
                More about us <i class="fa fa-angle-right" aria-hidden="true"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ================= CLOSING CTA ================= --}}
  <section class="cta-section home-section">
    <div class="container">
      <div class="cta-band glass-card">
        <div>
          <h3>Hungry now?</h3>
          <p>The full menu is one click away — filter it by category, price, or whatever you are craving.</p>
        </div>

        <div class="cta-actions">
          <a href="{{ route('menu.index') }}" class="view-menu-btn">
            Order food <i class="fa fa-angle-right" aria-hidden="true"></i>
          </a>
          <a href="{{ route('contact.page') }}" class="cta-ghost">Talk to us</a>
        </div>
      </div>
    </div>
  </section>

@endsection
