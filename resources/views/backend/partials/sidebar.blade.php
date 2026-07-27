@php
    /**
     * Every entry is gated on a "<module>.view" permission, which is exactly
     * what the superadmin ticks on the admin-users screen — so hiding a
     * sidebar link and denying the page are the same decision, and nobody
     * ever sees a menu item that would only answer with a 403.
     */
    $counts = $sidebarCounts ?? [];

    $isActive = fn (...$patterns) => request()->routeIs(...$patterns);
@endphp

<nav class="nxl-navigation">
    <div class="navbar-wrapper">

        <div class="m-header">
            <a href="{{ route('admin.dashboard') }}" class="b-brand">
                <span class="brand-mark">
                    <i class="feather-hexagon"></i>
                </span>
                <span class="logo-text logo-lg">Feane<span class="logo-accent">Admin</span></span>
                <span class="logo-text logo-sm">F</span>
            </a>
        </div>

        <div class="navbar-content">
            <ul class="nxl-navbar">

                {{-- ============================ MAIN ============================ --}}
                <li class="nxl-item nxl-caption"><label>Main</label></li>

                <li class="nxl-item {{ $isActive('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-home"></i></span>
                        <span class="nxl-mtext">Dashboard</span>
                    </a>
                </li>

                {{-- ========================= OPERATIONS ========================= --}}
                @canany(['pos.view', 'pos.create', 'orders.view', 'delivery_men.view', 'delivery_runs.view'])
                    <li class="nxl-item nxl-caption"><label>Operations</label></li>
                @endcanany

                @canany(['pos.view', 'pos.create'])
                    <li class="nxl-item nxl-hasmenu {{ $isActive('admin.pos.*') ? 'active nxl-trigger' : '' }}">
                        <a href="javascript:void(0);" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-shopping-cart"></i></span>
                            <span class="nxl-mtext">POS Billing</span>
                            <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                        </a>
                        <ul class="nxl-submenu">
                            @can('pos.create')
                                <li class="nxl-item {{ $isActive('admin.pos.index') ? 'active' : '' }}">
                                    <a class="nxl-link" href="{{ route('admin.pos.index') }}">New Sale</a>
                                </li>
                            @endcan
                            @can('pos.view')
                                <li class="nxl-item {{ $isActive('admin.pos.sales') ? 'active' : '' }}">
                                    <a class="nxl-link" href="{{ route('admin.pos.sales') }}">Sales History</a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany

                @can('orders.view')
                    <li class="nxl-item {{ $isActive('admin.orders.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.orders.index') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-package"></i></span>
                            <span class="nxl-mtext">Orders</span>
                            @if (!empty($counts['orders']))
                                <span class="nxl-badge">{{ $counts['orders'] }}</span>
                            @endif
                        </a>
                    </li>
                @endcan

                @canany(['delivery_men.view', 'delivery_runs.view', 'delivery_runs.create'])
                    <li class="nxl-item nxl-hasmenu {{ $isActive('admin.delivery-men.*', 'admin.delivery-runs.*') ? 'active nxl-trigger' : '' }}">
                        <a href="javascript:void(0);" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-truck"></i></span>
                            <span class="nxl-mtext">Delivery</span>
                            <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                        </a>
                        <ul class="nxl-submenu">
                            @can('delivery_men.view')
                                <li class="nxl-item {{ $isActive('admin.delivery-men.*') ? 'active' : '' }}">
                                    <a class="nxl-link" href="{{ route('admin.delivery-men.index') }}">Delivery Men</a>
                                </li>
                            @endcan
                            @can('delivery_runs.view')
                                <li class="nxl-item {{ $isActive('admin.delivery-runs.index', 'admin.delivery-runs.show') ? 'active' : '' }}">
                                    <a class="nxl-link" href="{{ route('admin.delivery-runs.index') }}">Delivery Runs</a>
                                </li>
                            @endcan
                            @can('delivery_runs.create')
                                <li class="nxl-item {{ $isActive('admin.delivery-runs.create') ? 'active' : '' }}">
                                    <a class="nxl-link" href="{{ route('admin.delivery-runs.create') }}">Assign Delivery</a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany

                {{-- ========================== CATALOG =========================== --}}
                @canany(['foods.view', 'categories.view', 'subcategories.view', 'units.view'])
                    <li class="nxl-item nxl-caption"><label>Catalog</label></li>
                @endcanany

                @can('foods.view')
                    <li class="nxl-item nxl-hasmenu {{ $isActive('admin.foods.*') ? 'active nxl-trigger' : '' }}">
                        <a href="javascript:void(0);" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-shopping-bag"></i></span>
                            <span class="nxl-mtext">Foods</span>
                            @if (!empty($counts['foods']))
                                <span class="nxl-badge">{{ $counts['foods'] }}</span>
                            @endif
                            <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                        </a>
                        <ul class="nxl-submenu">
                            <li class="nxl-item {{ $isActive('admin.foods.index') ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('admin.foods.index') }}">All Foods</a>
                            </li>
                            <li class="nxl-item {{ $isActive('admin.foods.inactive') ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('admin.foods.inactive') }}">Inactive Foods</a>
                            </li>
                            @can('foods.create')
                                <li class="nxl-item {{ $isActive('admin.foods.create') ? 'active' : '' }}">
                                    <a class="nxl-link" href="{{ route('admin.foods.create') }}">Add Food</a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcan

                @can('categories.view')
                    <li class="nxl-item {{ $isActive('admin.category.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.category.index') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-grid"></i></span>
                            <span class="nxl-mtext">Categories</span>
                        </a>
                    </li>
                @endcan

                @can('subcategories.view')
                    <li class="nxl-item {{ $isActive('admin.subcategory.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.subcategory.index') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-layers"></i></span>
                            <span class="nxl-mtext">Sub Categories</span>
                        </a>
                    </li>
                @endcan

                @can('units.view')
                    <li class="nxl-item {{ $isActive('admin.units.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.units.index') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-sliders"></i></span>
                            <span class="nxl-mtext">Units</span>
                        </a>
                    </li>
                @endcan

                {{-- ========================= MARKETING ========================== --}}
                @canany(['coupons.view', 'promotions.view'])
                    <li class="nxl-item nxl-caption"><label>Marketing</label></li>
                @endcanany

                @can('coupons.view')
                    <li class="nxl-item {{ $isActive('admin.coupons.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.coupons.index') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-gift"></i></span>
                            <span class="nxl-mtext">Coupons &amp; Offers</span>
                        </a>
                    </li>
                @endcan

                @can('promotions.view')
                    <li class="nxl-item {{ $isActive('admin.promotions.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.promotions.index') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-image"></i></span>
                            <span class="nxl-mtext">Promo Banners</span>
                        </a>
                    </li>
                @endcan

                {{-- =========================== PEOPLE =========================== --}}
                @canany(['customers.view', 'account_requests.view', 'contact_messages.view'])
                    <li class="nxl-item nxl-caption"><label>People</label></li>
                @endcanany

                @can('customers.view')
                    <li class="nxl-item {{ $isActive('admin.registrations') ? 'active' : '' }}">
                        <a href="{{ route('admin.registrations') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-users"></i></span>
                            <span class="nxl-mtext">Customers</span>
                        </a>
                    </li>
                @endcan

                @can('account_requests.view')
                    <li class="nxl-item {{ $isActive('admin.account-requests.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.account-requests.index') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-inbox"></i></span>
                            <span class="nxl-mtext">Account Requests</span>
                            @if (!empty($counts['account_requests']))
                                <span class="nxl-badge">{{ $counts['account_requests'] }}</span>
                            @endif
                        </a>
                    </li>
                @endcan

                @can('contact_messages.view')
                    <li class="nxl-item {{ $isActive('admin.aboutus.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.aboutus.index') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-message-square"></i></span>
                            <span class="nxl-mtext">Contact Messages</span>
                        </a>
                    </li>
                @endcan

                {{-- ============================ SUPPORT ============================ --}}
                @can('chat.view')
                    <li class="nxl-item nxl-caption"><label>Support</label></li>

                    <li class="nxl-item {{ $isActive('admin.chat.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.chat.index') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-message-circle"></i></span>
                            <span class="nxl-mtext">Live Chat</span>
                            @if (!empty($counts['chat']))
                                <span class="nxl-badge">{{ $counts['chat'] }}</span>
                            @endif
                        </a>
                    </li>
                @endcan

                {{-- ========================= MONITORING ========================= --}}
                @canany(['activity_log.view', 'login_history.view', 'admin_login_history.view'])
                    <li class="nxl-item nxl-caption"><label>Monitoring</label></li>
                @endcanany

                @can('activity_log.view')
                    <li class="nxl-item {{ $isActive('admin.activity-log.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.activity-log.index') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-activity"></i></span>
                            <span class="nxl-mtext">Activity Log</span>
                        </a>
                    </li>
                @endcan

                @can('login_history.view')
                    <li class="nxl-item {{ $isActive('admin.login.history') ? 'active' : '' }}">
                        <a href="{{ route('admin.login.history') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-log-in"></i></span>
                            <span class="nxl-mtext">Customer Logins</span>
                        </a>
                    </li>
                @endcan

                @can('admin_login_history.view')
                    <li class="nxl-item {{ $isActive('admin.admin-login-history.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.admin-login-history.index') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-shield"></i></span>
                            <span class="nxl-mtext">Admin Logins</span>
                        </a>
                    </li>
                @endcan

                {{-- =========================== SYSTEM =========================== --}}
                @canany(['manage-admins', 'manage-admin-requests'])
                    <li class="nxl-item nxl-caption"><label>System</label></li>
                @endcanany

                {{-- Superadmin-only: admins locked out of the panel. --}}
                @can('manage-admin-requests')
                    <li class="nxl-item {{ $isActive('admin.password-reset-requests.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.password-reset-requests.index') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-key"></i></span>
                            <span class="nxl-mtext">Password Requests</span>
                            @if (!empty($counts['password_reset_requests']))
                                <span class="nxl-badge">{{ $counts['password_reset_requests'] }}</span>
                            @endif
                        </a>
                    </li>

                    <li class="nxl-item {{ $isActive('admin.admin-activation-requests.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.admin-activation-requests.index') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-user-check"></i></span>
                            <span class="nxl-mtext">Activation Requests</span>
                            @if (!empty($counts['admin_activation_requests']))
                                <span class="nxl-badge">{{ $counts['admin_activation_requests'] }}</span>
                            @endif
                        </a>
                    </li>
                @endcan

                @can('manage-admins')
                    <li class="nxl-item nxl-hasmenu {{ $isActive('admin.admin-users.*') ? 'active nxl-trigger' : '' }}">
                        <a href="javascript:void(0);" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-shield"></i></span>
                            <span class="nxl-mtext">Admins &amp; Roles</span>
                            <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                        </a>
                        <ul class="nxl-submenu">
                            <li class="nxl-item {{ $isActive('admin.admin-users.index') ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('admin.admin-users.index') }}">Manage Admins</a>
                            </li>
                            <li class="nxl-item {{ $isActive('admin.admin-users.create') ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ route('admin.admin-users.create') }}">Add Admin</a>
                            </li>
                        </ul>
                    </li>
                @endcan
            </ul>

            {{-- ============================ FOOTER ============================ --}}
            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <span class="avatar-initials sm">
                        {{ Str::of(auth()->user()->name ?? 'A')->substr(0, 2) }}
                    </span>
                    <div class="sidebar-user-text">
                        <p class="sidebar-user-name">{{ auth()->user()->name ?? 'Admin' }}</p>
                        <p class="sidebar-user-role">{{ auth()->user()?->roleLabel() }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.logout') }}" class="logout-form">
                    @csrf
                    <button type="submit" class="btn btn-soft-danger w-100 btn-sm">
                        <i class="feather-log-out"></i>
                        <span>Log out</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<style>
    .nxl-navigation .m-header .b-brand {
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
    }

    .nxl-navigation .brand-mark {
        width: 34px;
        height: 34px;
        border-radius: 11px;
        display: grid;
        place-items: center;
        background: linear-gradient(135deg, var(--ar-primary), var(--ar-accent));
        color: #fff;
        font-size: 16px;
        flex-shrink: 0;
        box-shadow: 0 6px 16px var(--ar-primary-glow);
    }

    .nxl-navigation .logo-text {
        font-weight: 800;
        letter-spacing: -.02em;
        color: var(--ar-ink);
        font-size: 18px;
    }

    .nxl-navigation .logo-accent { color: var(--ar-primary); }
    .nxl-navigation .logo-sm { font-size: 17px; }

    .sidebar-user {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
        min-width: 0;
    }

    .sidebar-user-text { min-width: 0; }

    .sidebar-user-name {
        margin: 0;
        font-size: 13px;
        font-weight: 700;
        color: var(--ar-ink);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sidebar-user-role {
        margin: 1px 0 0;
        font-size: 11.5px;
        color: var(--ar-muted);
    }

    /* Collapsed rail: hide the wordy bits, keep the avatar. */
    .minimenu .sidebar-footer { padding: 12px 10px 16px; }
    .minimenu .sidebar-user-text,
    .minimenu .sidebar-footer button span { display: none; }
    .minimenu .nxl-badge { display: none; }
</style>
