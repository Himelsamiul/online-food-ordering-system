@php
    $cartCount = count(session('cart', []));
    $customer  = Auth::guard('frontend')->user();
@endphp

{{-- The stylesheet is linked in master.blade.php's <head>; a @push from here
     would land after @stack('styles') had already rendered. --}}
@once
    @push('scripts')
        <script src="{{ asset('assets/js/notifications.js') }}"></script>
    @endpush
@endonce

{{--
    The old header put the account links inside the centre nav list and gave
    .navbar-nav a hard `padding-left: 18%`. With eight items plus the search
    box that overflowed the row and wrapped the brand onto its own line — the
    "broken header". Account controls now live in .user_option where they
    belong, and the padding is removed in storefront-refresh.css.
--}}
<header class="header_section sf-header">
    <div class="container">
        <nav class="navbar navbar-expand-lg custom_nav-container">

            <a class="navbar-brand" href="{{ route('home') }}">
                <span>Feane</span>
            </a>

            <button class="navbar-toggler" type="button" data-toggle="collapse"
                    data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="Toggle navigation">
                <span></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">

                {{-- ---------------------------- primary nav ---------------------------- --}}
                <ul class="navbar-nav sf-nav mx-auto">
                    <li class="nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('home') }}">Home</a>
                    </li>

                    <li class="nav-item {{ request()->routeIs('menu.*', 'food.details') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('menu.index') }}">Menu</a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="categoryDropdown" role="button"
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Category
                        </a>

                        <div class="dropdown-menu sf-dropdown" aria-labelledby="categoryDropdown">
                            @forelse (($categories ?? []) as $category)
                                <a class="dropdown-item"
                                   href="{{ route('menu.index', ['category' => $category->id]) }}">
                                    {{ $category->name }}
                                </a>
                            @empty
                                <span class="dropdown-item disabled">No category available</span>
                            @endforelse
                        </div>
                    </li>

                    <li class="nav-item {{ request()->routeIs('about') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('about') }}">About</a>
                    </li>

                    <li class="nav-item {{ request()->routeIs('contact.page') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('contact.page') }}">Contact</a>
                    </li>
                </ul>

                {{-- --------------------------- utility cluster -------------------------- --}}
                <div class="user_option sf-actions">

                    <form class="sf-search" action="{{ route('menu.index') }}" method="GET" role="search">
                        <i class="fa fa-search" aria-hidden="true"></i>
                        <input type="text" name="q" value="{{ request('q') }}"
                               placeholder="Search food…" aria-label="Search food">
                    </form>

                    <a class="cart_link sf-icon-link" href="{{ route('cart.index') }}" aria-label="Cart">
                        <svg width="20" height="20" viewBox="0 0 456.029 456.029" fill="currentColor"
                             xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M345.6,338.862c-29.184,0-53.248,23.552-53.248,53.248c0,29.184,23.552,53.248,53.248,53.248
                                     c29.184,0,53.248-23.552,53.248-53.248C398.336,362.926,374.784,338.862,345.6,338.862z" />
                            <path d="M439.296,84.91c-1.024,0-2.56-0.512-4.096-0.512H112.64l-5.12-34.304C104.448,27.566,84.992,10.67,61.952,10.67H20.48
                                     C9.216,10.67,0,19.886,0,31.15c0,11.264,9.216,20.48,20.48,20.48h41.472c2.56,0,4.608,2.048,5.12,4.608l31.744,216.064
                                     c4.096,27.136,27.648,47.616,55.296,47.616h212.992c26.624,0,49.664-18.944,55.296-45.056l33.28-166.4
                                     C457.728,97.71,450.56,86.958,439.296,84.91z" />
                            <path d="M215.04,389.55c-1.024-28.16-24.576-50.688-52.736-50.688c-29.696,1.536-52.224,26.112-51.2,55.296
                                     c1.024,28.16,24.064,50.688,52.224,50.688h1.024C193.536,443.31,216.576,418.734,215.04,389.55z" />
                        </svg>

                        <span class="cart-count-badge" id="cartCount"
                              style="{{ $cartCount > 0 ? '' : 'display:none;' }}">{{ $cartCount }}</span>
                    </a>

                    @auth('frontend')
                        {{-- Notification bell. Rendered empty and filled by the
                             first poll, so the header never blocks on a query. --}}
                        <div class="sf-bell-wrap" id="sfBell"
                             data-poll-url="{{ route('notifications.poll') }}"
                             data-read-url="{{ route('notifications.read-all') }}"
                             data-all-url="{{ route('notifications.index') }}"
                             data-interval="{{ (int) config('security.notifications.poll_ms', 25000) }}">

                            <button type="button" class="sf-icon-link sf-bell-toggle"
                                    aria-label="Notifications" aria-expanded="false">
                                <i class="fa fa-bell" aria-hidden="true"></i>
                                <span class="sf-bell-badge" hidden>0</span>
                            </button>

                            <div class="sf-bell-panel" hidden>
                                <div class="sf-bell-head">
                                    <strong>Notifications</strong>
                                    <button type="button" class="sf-bell-readall" hidden>Mark all read</button>
                                </div>

                                <div class="sf-bell-list">
                                    <div class="sf-bell-loading">
                                        <span class="sf-bell-spinner" aria-hidden="true"></span> Loading…
                                    </div>
                                </div>

                                <a href="{{ route('notifications.index') }}" class="sf-bell-foot">
                                    See all notifications
                                </a>
                            </div>
                        </div>

                        <div class="dropdown sf-account">
                            <a href="#" class="sf-account-toggle" id="accountDropdown" role="button"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="sf-avatar">
                                    @if ($customer->image && file_exists(public_path('storage/' . $customer->image)))
                                        <img src="{{ asset('storage/' . $customer->image) }}" alt="">
                                    @else
                                        {{ Str::of($customer->full_name)->substr(0, 1) }}
                                    @endif
                                </span>
                                <span class="sf-account-name d-none d-lg-inline">
                                    {{ Str::of($customer->full_name)->before(' ') }}
                                </span>
                                <i class="fa fa-angle-down d-none d-lg-inline" aria-hidden="true"></i>
                            </a>

                            <div class="dropdown-menu dropdown-menu-right sf-dropdown"
                                 aria-labelledby="accountDropdown">
                                <div class="sf-dropdown-head">
                                    <strong>{{ $customer->full_name }}</strong>
                                    <span>{{ $customer->email }}</span>
                                </div>

                                <a class="dropdown-item" href="{{ route('profile') }}">
                                    <i class="fa fa-user" aria-hidden="true"></i> My Profile
                                </a>
                                <a class="dropdown-item" href="{{ route('cart.index') }}">
                                    <i class="fa fa-shopping-cart" aria-hidden="true"></i> My Cart
                                </a>
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    <i class="fa fa-cog" aria-hidden="true"></i> Edit Profile
                                </a>

                                <div class="dropdown-divider"></div>

                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item sf-logout">
                                        <i class="fa fa-sign-out" aria-hidden="true"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endauth

                    @guest('frontend')
                        <a class="sf-btn sf-btn-ghost" href="{{ route('login') }}">Login</a>
                        <a class="sf-btn sf-btn-solid" href="{{ route('register') }}">Sign Up</a>
                    @endguest
                </div>
            </div>
        </nav>
    </div>
</header>
