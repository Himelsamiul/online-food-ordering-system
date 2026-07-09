<style>
    .logo-text {
        font-weight: 700;
        letter-spacing: 1px;
        color: #0d0d0d;
    }

    .logo-lg {
        font-size: 20px;
    }

    .logo-sm {
        font-size: 18px;
    }
</style>

<nav class="nxl-navigation">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="index.html" class="b-brand">
                <!-- ======== change your logo here ============ -->
                <span class="logo-text logo-lg">Duralux</span>
                <span class="logo-text logo-sm">DL</span>
            </a>
        </div>

        <div class="navbar-content">
            <ul class="nxl-navbar">

                <li class="nxl-item nxl-caption">
                    <label>Navigation</label>
                </li>

                {{-- Dashboard --}}
                <li class="nxl-item {{ request()->routeIs('admin.dashboard.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}" class="nxl-link">
                        <span class="nxl-micon">
                            <i class="feather-airplay"></i>
                        </span>
                        <span class="nxl-mtext">Dashboard</span>
                    </a>
                </li>

                {{-- POS --}}
                @can('pos')
                <li class="nxl-item nxl-hasmenu {{ request()->routeIs('admin.pos.*') ? 'active nxl-trigger' : '' }}">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon">
                            <i class="feather-shopping-cart"></i>
                        </span>
                        <span class="nxl-mtext">POS Billing</span>
                        <span class="nxl-arrow">
                            <i class="feather-chevron-right"></i>
                        </span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="nxl-item {{ request()->routeIs('admin.pos.index') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('admin.pos.index') }}">
                                New Sale
                            </a>
                        </li>
                        <li class="nxl-item {{ request()->routeIs('admin.pos.sales') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('admin.pos.sales') }}">
                                Sales History
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan

                {{-- Category --}}
                @can('categories')
                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon">
                            <i class="feather-grid"></i>
                        </span>
                        <span class="nxl-mtext">Category</span>
                        <span class="nxl-arrow">
                            <i class="feather-chevron-right"></i>
                        </span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('admin.category.index') }}">
                                Category Index
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan

                {{-- Sub Category --}}
                @can('subcategories')
                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon">
                            <i class="feather-layers"></i>
                        </span>
                        <span class="nxl-mtext">Sub Category</span>
                        <span class="nxl-arrow">
                            <i class="feather-chevron-right"></i>
                        </span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('admin.subcategory.index') }}">
                                Sub Category Index
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan

                {{-- Units --}}
                @can('units')
                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon">
                            <i class="feather-sliders"></i>
                        </span>
                        <span class="nxl-mtext">Units</span>
                        <span class="nxl-arrow">
                            <i class="feather-chevron-right"></i>
                        </span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('admin.units.index') }}">
                                Units Index
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan

                {{-- Food --}}
                @can('foods')
                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon">
                            <i class="feather-shopping-bag"></i>
                        </span>
                        <span class="nxl-mtext">Food</span>
                        <span class="nxl-arrow">
                            <i class="feather-chevron-right"></i>
                        </span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('admin.foods.index') }}">
                                Food Index
                            </a>
                        </li>
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('admin.foods.inactive') }}">
                                Inactive Foods
                            </a>
                        </li>
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('admin.foods.create') }}">
                                Food Create
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan

                {{-- Orders --}}
                @can('orders')
                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon">
                            <i class="feather-shopping-cart"></i>
                        </span>
                        <span class="nxl-mtext">Orders</span>
                        <span class="nxl-arrow">
                            <i class="feather-chevron-right"></i>
                        </span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('admin.orders.index') }}">
                                Orders Index
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan

                {{-- Delivery Man --}}
                @can('delivery_men')
                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon">
                            <i class="feather-truck"></i>
                        </span>
                        <span class="nxl-mtext">Delivery Man</span>
                        <span class="nxl-arrow">
                            <i class="feather-chevron-right"></i>
                        </span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('admin.delivery-men.index') }}">
                                Delivery Man Index
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan

                {{-- Delivery Assign --}}
                @can('delivery_runs')
                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon">
                            <i class="feather-map"></i>
                        </span>
                        <span class="nxl-mtext">Delivery Assign</span>
                        <span class="nxl-arrow">
                            <i class="feather-chevron-right"></i>
                        </span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('admin.delivery-runs.create') }}">
                                Delivery create
                            </a>
                        </li>
                    </ul>
                    <ul class="nxl-submenu">
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('admin.delivery-runs.index') }}">
                                Delivery Index
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan


                {{-- Customers --}}
                @can('customers')
                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon">
                            <i class="feather-users"></i>
                        </span>
                        <span class="nxl-mtext">Customers</span>
                        <span class="nxl-arrow">
                            <i class="feather-chevron-right"></i>
                        </span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('admin.registrations') }}">
                                Customers
                            </a>
                        </li>
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('admin.login.history') }}">
                                Customers Login History
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan

                {{-- Contact Messages --}}
                @can('contact_messages')
                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon">
                            <i class="feather-life-buoy"></i>
                        </span>
                        <span class="nxl-mtext">Contacts Message</span>
                        <span class="nxl-arrow">
                            <i class="feather-chevron-right"></i>
                        </span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('admin.aboutus.index') }}">
                                Messages
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan

                {{-- Admin Users & Roles (superadmin only) --}}
                @can('manage-admins')
                <li class="nxl-item nxl-hasmenu {{ request()->routeIs('admin.admin-users.*') ? 'active nxl-trigger' : '' }}">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon">
                            <i class="feather-shield"></i>
                        </span>
                        <span class="nxl-mtext">Admin Users</span>
                        <span class="nxl-arrow">
                            <i class="feather-chevron-right"></i>
                        </span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="nxl-item {{ request()->routeIs('admin.admin-users.index') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('admin.admin-users.index') }}">
                                Manage Admins
                            </a>
                        </li>
                        <li class="nxl-item {{ request()->routeIs('admin.admin-users.create') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('admin.admin-users.create') }}">
                                Add Admin
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan

                {{-- Logout --}}
                <li class="nxl-item">
                    <form method="POST"
                          action="{{ route('admin.logout') }}"
                          class="logout-form">
                        @csrf
                        <button type="submit"
                                class="nxl-link w-100 text-start border-0 bg-transparent">
                            <span class="nxl-micon">
                                <i class="feather-power"></i>
                            </span>
                            <span class="nxl-mtext">Logout</span>
                        </button>
                    </form>
                </li>

            </ul>
        </div>
    </div>
</nav>
