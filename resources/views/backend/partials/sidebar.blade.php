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

                {{-- Category --}}
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

                {{-- Sub Category --}}
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

                {{-- Units --}}
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

                {{-- Food --}}
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

                {{-- Orders --}}
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

                {{-- Payment --}}
                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon">
                            <i class="feather-dollar-sign"></i>
                        </span>
                        <span class="nxl-mtext">Payment</span>
                        <span class="nxl-arrow">
                            <i class="feather-chevron-right"></i>
                        </span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="nxl-item">
                            <a class="nxl-link" href="payment.html">Payment</a>
                        </li>
                        <li class="nxl-item">
                            <a class="nxl-link" href="invoice-view.html">Invoice View</a>
                        </li>
                        <li class="nxl-item">
                            <a class="nxl-link" href="invoice-create.html">Invoice Create</a>
                        </li>
                    </ul>
                </li>

                {{-- Customers --}}
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
                        <li class="nxl-item">
                            <a class="nxl-link" href="customers-create.html">
                                Customers Create
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- Contact Messages --}}
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
                            <a class="nxl-link" href="#">
                                Messages
                            </a>
                        </li>
                    </ul>
                </li>

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
