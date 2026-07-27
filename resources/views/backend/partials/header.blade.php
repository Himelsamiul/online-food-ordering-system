@php
    /** @var array{items: \Illuminate\Support\Collection, total: int, unread: int} $notifications */
    $feed   = $notifications ?? ['items' => collect(), 'total' => 0, 'unread' => 0];
    $admin  = auth()->user();
    $unread = $feed['unread'];
@endphp

<header class="nxl-header">
    <div class="header-wrapper">

        {{-- ============================ LEFT ============================ --}}
        <div class="header-left d-flex align-items-center gap-3">
            <a href="javascript:void(0);" class="nxl-head-mobile-toggler" id="mobile-collapse">
                <div class="hamburger hamburger--arrowturn">
                    <div class="hamburger-box">
                        <div class="hamburger-inner"></div>
                    </div>
                </div>
            </a>

            <div class="nxl-navigation-toggle">
                <a href="javascript:void(0);" id="menu-mini-button" title="Collapse menu">
                    <i class="feather-align-left"></i>
                </a>
                <a href="javascript:void(0);" id="menu-expend-button" style="display: none" title="Expand menu">
                    <i class="feather-arrow-right"></i>
                </a>
            </div>

            {{-- Global search jumps straight into the food catalogue, which is
                 the only list big enough to need one. --}}
            @can('foods.view')
                <form class="header-search d-none d-md-flex" action="{{ route('admin.foods.index') }}" method="GET">
                    <i class="feather-search"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search foods…" aria-label="Search foods">
                </form>
            @endcan
        </div>

        {{-- =========================== RIGHT =========================== --}}
        <div class="header-right ms-auto">
            <div class="d-flex align-items-center gap-2">

                {{-- Full screen --}}
                <div class="nxl-h-item d-none d-sm-flex">
                    <a href="javascript:void(0);" class="nxl-head-link me-0"
                       onclick="$('body').fullScreenHelper('toggle');" title="Toggle full screen">
                        <i class="feather-maximize maximize"></i>
                        <i class="feather-minimize minimize"></i>
                    </a>
                </div>

                {{-- Dark / light --}}
                <button type="button" class="theme-toggle" data-theme-toggle
                        aria-pressed="false" title="Switch to dark mode">
                    <i class="feather-moon icon-moon"></i>
                    <i class="feather-sun icon-sun"></i>
                </button>

                {{-- ===================== NOTIFICATIONS ===================== --}}
                <div class="dropdown nxl-h-item" data-notif-root
                     data-notif-read-url="{{ route('admin.notifications.read') }}"
                     data-notif-feed-url="{{ route('admin.notifications.feed') }}">

                    <a href="javascript:void(0);" class="nxl-head-link me-0 position-relative"
                       data-bs-toggle="dropdown" role="button" data-bs-auto-close="outside"
                       aria-label="Notifications">
                        <i class="feather-bell"></i>
                        <span class="badge bg-danger nxl-h-badge" data-notif-badge
                              style="{{ $unread > 0 ? '' : 'display:none;' }}">
                            {{ $unread > 99 ? '99+' : $unread }}
                        </span>
                    </a>

                    <div class="dropdown-menu dropdown-menu-end notif-menu">
                        <div class="notif-head">
                            <h6>
                                Notifications
                                @if ($feed['total'])
                                    <span class="text-muted fw-normal">({{ $feed['total'] }})</span>
                                @endif
                            </h6>
                            @if ($unread > 0)
                                <button type="button" class="notif-clear" data-notif-clear>Mark all read</button>
                            @endif
                        </div>

                        <div class="notif-body ar-scroll">
                            @forelse ($feed['items'] as $item)
                                <a href="{{ $item['url'] }}"
                                   class="notif-item {{ $item['is_new'] ? 'is-new' : '' }}">
                                    <span class="notif-icon tone-{{ $item['tone'] }}">
                                        <i class="{{ $item['icon'] }}"></i>
                                    </span>
                                    <span class="notif-text">
                                        <span class="notif-title d-block">{{ $item['title'] }}</span>
                                        <span class="notif-sub d-block">{{ $item['body'] }}</span>
                                        <span class="notif-time d-block">
                                            {{ $item['time']?->diffForHumans() ?? '' }}
                                        </span>
                                    </span>
                                </a>
                            @empty
                                <div class="notif-empty">
                                    <i class="feather-check-circle"></i>
                                    Nothing needs your attention right now.
                                </div>
                            @endforelse
                        </div>

                        @can('activity_log.view')
                            <div class="notif-foot">
                                <a href="{{ route('admin.activity-log.index') }}">View full activity log</a>
                            </div>
                        @endcan
                    </div>
                </div>

                {{-- ======================== PROFILE ======================== --}}
                <div class="dropdown nxl-h-item">
                    <a href="javascript:void(0);" data-bs-toggle="dropdown" role="button"
                       data-bs-auto-close="outside" class="d-flex align-items-center gap-2">
                        <span class="avatar-initials sm">{{ Str::of($admin->name)->substr(0, 2) }}</span>
                    </a>

                    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-user-dropdown">
                        <div class="dropdown-header">
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar-initials">{{ Str::of($admin->name)->substr(0, 2) }}</span>
                                <div class="min-w-0">
                                    <h6 class="mb-0 text-ink">
                                        {{ $admin->name }}
                                        <span class="badge {{ $admin->isSuperadmin() ? 'bg-primary' : 'bg-secondary' }} ms-1">
                                            {{ $admin->isSuperadmin() ? 'SUPER' : 'ADMIN' }}
                                        </span>
                                    </h6>
                                    <span class="fs-12 text-muted">{{ $admin->email }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="dropdown-divider"></div>

                        <a href="{{ route('admin.dashboard') }}" class="dropdown-item">
                            <i class="feather-home"></i><span>Dashboard</span>
                        </a>

                        @can('manage-admins')
                            <a href="{{ route('admin.admin-users.edit', $admin->id) }}" class="dropdown-item">
                                <i class="feather-user"></i><span>My account &amp; password</span>
                            </a>
                        @endcan

                        @can('admin_login_history.view')
                            <a href="{{ route('admin.admin-login-history.index') }}" class="dropdown-item">
                                <i class="feather-shield"></i><span>My sign-in history</span>
                            </a>
                        @endcan

                        <a href="javascript:void(0);" class="dropdown-item" data-theme-toggle>
                            <i class="feather-moon"></i><span>Toggle dark mode</span>
                        </a>

                        <div class="dropdown-divider"></div>

                        <form action="{{ route('admin.logout') }}" method="POST" class="logout-form">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger w-100 border-0 bg-transparent">
                                <i class="feather-log-out"></i><span>Log out</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<style>
    .nxl-header .dropdown-header { padding: 14px 12px 10px; }
    .nxl-header .min-w-0 { min-width: 0; }
    .nxl-header .nxl-user-dropdown { width: 272px; }
    .nxl-header .nxl-user-dropdown h6 { font-size: 14px; font-weight: 700; }
    .nxl-header .nxl-user-dropdown .fs-12 {
        font-size: 12px;
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .nxl-header .header-wrapper { gap: 12px; }
</style>
