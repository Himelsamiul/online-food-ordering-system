@extends('backend.master')

@section('title', 'Dashboard')

@section('content')

@php
    /** @var \App\Models\User $admin */
    $admin = auth()->user();

    $hour     = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');

    // Stat cards double as navigation, so a card only becomes a link when the
    // admin is actually allowed through the door on the other side.
    $canOrders    = $admin->hasPermission('orders.view');
    $canFoods     = $admin->hasPermission('foods.view');
    $canCategories= $admin->hasPermission('categories.view');
    $canCustomers = $admin->hasPermission('customers.view');
    $canRiders    = $admin->hasPermission('delivery_men.view');
    $canMessages  = $admin->hasPermission('contact_messages.view');

    $pipeline = [
        ['key' => 'pending',          'label' => 'Pending',          'count' => $pendingOrders,        'tone' => 'warning', 'icon' => 'feather-clock'],
        ['key' => 'cooking',          'label' => 'Cooking',          'count' => $cookingOrders,        'tone' => 'info',    'icon' => 'feather-coffee'],
        ['key' => 'out_for_delivery', 'label' => 'Out for Delivery', 'count' => $outForDeliveryOrders, 'tone' => 'primary', 'icon' => 'feather-truck'],
        ['key' => 'delivered',        'label' => 'Delivered',        'count' => $deliveredOrders,      'tone' => 'success', 'icon' => 'feather-check-circle'],
        ['key' => 'cancelled',        'label' => 'Cancelled',        'count' => $cancelledOrders,      'tone' => 'danger',  'icon' => 'feather-x-circle'],
    ];

    $statusBadge = [
        'pending'          => 'bg-warning',
        'cooking'          => 'bg-info',
        'out_for_delivery' => 'bg-primary',
        'delivered'        => 'bg-success',
        'cancelled'        => 'bg-danger',
    ];

    $statusLabel = collect($pipeline)->pluck('label', 'key')->all();
@endphp

<div class="container-fluid">

    <x-page-header
        :title="$greeting . ', ' . $admin->name"
        :subtitle="now()->format('l, d F Y') . ' — a snapshot of revenue, orders and stock.'"
        icon="feather-grid">
        <span class="badge {{ $admin->isSuperadmin() ? 'bg-primary' : 'bg-info' }}">
            <i class="feather-shield"></i> {{ $admin->roleLabel() }}
        </span>
    </x-page-header>

    {{-- ================= HERO STATS ================= --}}
    <div class="stat-grid">
        <x-stat-card
            label="Revenue"
            :value="number_format($totalOrderAmount, 2)"
            prefix="BDT "
            icon="feather-dollar-sign"
            tone="success"
            foot="Paid orders only"
            :href="$canOrders ? route('admin.orders.index', ['payment_status' => 'paid']) : null" />

        <x-stat-card
            label="Total Orders"
            :value="number_format($totalOrders)"
            icon="feather-shopping-bag"
            tone="primary"
            :foot="number_format($deliveredOrders) . ' delivered so far'"
            :href="$canOrders ? route('admin.orders.index') : null" />

        <x-stat-card
            label="Pending Orders"
            :value="number_format($pendingOrders)"
            icon="feather-clock"
            tone="warning"
            foot="Waiting to be accepted"
            :href="$canOrders ? route('admin.orders.index', ['order_status' => 'pending']) : null" />

        <x-stat-card
            label="Customers"
            :value="number_format($totalUsers)"
            icon="feather-users"
            tone="info"
            :foot="number_format($activeUsers) . ' active &middot; ' . number_format($inactiveUsers) . ' inactive'"
            :href="$canCustomers ? route('admin.registrations') : null" />
    </div>

    {{-- ================= CATALOGUE & OPERATIONS ================= --}}
    <div class="stat-grid">
        <x-stat-card
            label="Foods"
            :value="number_format($totalFoods)"
            icon="feather-coffee"
            tone="primary"
            :foot="number_format($totalSubcategories) . ' subcategories &middot; ' . number_format($totalUnits) . ' units'"
            :href="$canFoods ? route('admin.foods.index') : null" />

        <x-stat-card
            label="Categories"
            :value="number_format($totalcategory)"
            icon="feather-tag"
            tone="info"
            foot="Top level of the menu"
            :href="$canCategories ? route('admin.category.index') : null" />

        <x-stat-card
            label="Delivery Men"
            :value="number_format($totalDeliveryMen)"
            icon="feather-truck"
            tone="success"
            :foot="number_format($activeDeliveryRuns) . ' of ' . number_format($totalDeliveryRuns) . ' runs on the way'"
            :href="$canRiders ? route('admin.delivery-men.index') : null" />

        <x-stat-card
            label="Contact Messages"
            :value="number_format($totalcontactmessage)"
            icon="feather-message-square"
            tone="warning"
            foot="Sent from the storefront"
            :href="$canMessages ? route('admin.aboutus.index') : null" />
    </div>

    {{-- ================= ORDER PIPELINE ================= --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5>Order Pipeline</h5>
            <span class="text-muted fs-13">{{ number_format($totalOrders) }} orders in total</span>
        </div>

        <div class="card-body">
            <div class="dash-tiles">
                @foreach ($pipeline as $stage)
                    @if ($canOrders)
                        <a class="dash-tile tone-{{ $stage['tone'] }}"
                           href="{{ route('admin.orders.index', ['order_status' => $stage['key']]) }}">
                            <span class="dash-tile-icon"><i class="{{ $stage['icon'] }}"></i></span>
                            <span>
                                <span class="dash-tile-value">{{ number_format($stage['count']) }}</span>
                                <span class="dash-tile-label">{{ $stage['label'] }}</span>
                            </span>
                        </a>
                    @else
                        <div class="dash-tile tone-{{ $stage['tone'] }}">
                            <span class="dash-tile-icon"><i class="{{ $stage['icon'] }}"></i></span>
                            <span>
                                <span class="dash-tile-value">{{ number_format($stage['count']) }}</span>
                                <span class="dash-tile-label">{{ $stage['label'] }}</span>
                            </span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- ================= TREND + PAYMENTS ================= --}}
    <div class="row g-4 mb-4">

        <div class="col-xxl-8 col-xl-7">
            <div class="card h-100">
                <div class="card-header">
                    <h5>Revenue &amp; Orders</h5>
                    <span class="text-muted fs-13">Last 14 days</span>
                </div>
                <div class="card-body">
                    <div id="dashTrendChart" class="dash-chart"></div>
                </div>
            </div>
        </div>

        <div class="col-xxl-4 col-xl-5">
            <div class="card h-100">
                <div class="card-header">
                    <h5>Payment Breakdown</h5>
                </div>
                <div class="card-body">

                    <p class="section-title">Cash on Delivery</p>
                    <div class="kv-list">
                        <div class="kv-row">
                            <span class="kv-key">Orders</span>
                            <span class="kv-val">{{ number_format($codOrdersCount) }}</span>
                        </div>
                        <div class="kv-row">
                            <span class="kv-key">Total Value</span>
                            <span class="kv-val">BDT {{ number_format($codTotalAmount, 2) }}</span>
                        </div>
                        <div class="kv-row">
                            <span class="kv-key">Collected</span>
                            <span class="kv-val" style="color:var(--ar-success)">
                                BDT {{ number_format($codCollectedAmount, 2) }}
                            </span>
                        </div>
                        <div class="kv-row">
                            <span class="kv-key">Still Owed</span>
                            <span class="kv-val" style="color:var(--ar-warning)">
                                BDT {{ number_format($codPendingAmount, 2) }}
                            </span>
                        </div>
                    </div>

                    <hr class="hr-soft">

                    <p class="section-title">Stripe</p>
                    <div class="kv-list">
                        <div class="kv-row">
                            <span class="kv-key">Orders</span>
                            <span class="kv-val">{{ number_format($stripeOrdersCount) }}</span>
                        </div>
                        <div class="kv-row">
                            <span class="kv-key">Total Paid</span>
                            <span class="kv-val" style="color:var(--ar-success)">
                                BDT {{ number_format($stripeTotalAmount, 2) }}
                            </span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ================= LIVE PANELS ================= --}}
    <div class="row g-4">

        {{-- ---------- Recent orders ---------- --}}
        @can('orders.view')
            <div class="col-xxl-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5>Recent Orders</h5>
                        <span class="text-muted fs-13">Latest {{ $recentOrders->count() }}</span>
                    </div>

                    @if ($recentOrders->isEmpty())
                        <div class="card-body">
                            <x-empty-state
                                icon="feather-shopping-bag"
                                title="No orders yet"
                                message="New orders show up here the moment a customer checks out." />
                        </div>
                    @else
                        <div class="table-scroll">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Order</th>
                                        <th>Customer</th>
                                        <th class="num">Amount</th>
                                        <th>Status</th>
                                        <th>Placed</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentOrders as $order)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.orders.show', $order) }}" class="fw-650">
                                                    #{{ $order->order_number }}
                                                </a>
                                            </td>
                                            <td>
                                                <div class="cell-media">
                                                    <span class="avatar-initials sm">
                                                        {{ \Illuminate\Support\Str::of((string) $order->name)->substr(0, 2) }}
                                                    </span>
                                                    <div>
                                                        <p class="cell-title">{{ $order->name }}</p>
                                                        <p class="cell-sub">{{ $order->phone }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="num">BDT {{ number_format((float) $order->total_amount, 2) }}</td>
                                            <td>
                                                <span class="badge {{ $statusBadge[$order->order_status] ?? 'bg-secondary' }}">
                                                    {{ $statusLabel[$order->order_status] ?? \Illuminate\Support\Str::headline((string) $order->order_status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted fs-13"
                                                      title="{{ $order->created_at?->format('d M Y, h:i A') }}">
                                                    {{ $order->created_at?->diffForHumans() }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <div class="card-footer">
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-soft btn-sm">
                            All orders <i class="feather-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        @endcan

        {{-- ---------- Recent activity ---------- --}}
        @can('activity_log.view')
            <div class="col-xxl-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5>Recent Activity</h5>
                        <span class="text-muted fs-13">Newest first</span>
                    </div>

                    <div class="card-body">
                        @if ($recentActivity->isEmpty())
                            <x-empty-state
                                icon="feather-activity"
                                title="Nothing logged yet"
                                message="Actions taken in the admin panel are recorded here." />
                        @else
                            <div class="timeline">
                                @foreach ($recentActivity as $log)
                                    <div class="timeline-item">
                                        <span class="timeline-dot tone-{{ $log->tone() }}">
                                            <i class="{{ $log->icon() }}"></i>
                                        </span>
                                        <p class="timeline-title">
                                            {{ $log->description ?: $log->eventLabel() . ' ' . $log->subjectName() }}
                                        </p>
                                        <p class="timeline-meta">
                                            {{ $log->user_name ?: 'System' }}
                                            &middot;
                                            <span title="{{ $log->created_at?->format('d M Y, h:i A') }}">
                                                {{ $log->created_at?->diffForHumans() }}
                                            </span>
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="card-footer">
                        <a href="{{ route('admin.activity-log.index') }}" class="btn btn-soft btn-sm">
                            Full activity log <i class="feather-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        @endcan

        {{-- ---------- Pending account requests ---------- --}}
        @can('account_requests.view')
            <div class="col-xxl-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5>Pending Account Requests</h5>
                        <span class="text-muted fs-13">{{ $pendingRequests->count() }} waiting</span>
                    </div>

                    @if ($pendingRequests->isEmpty())
                        <div class="card-body">
                            <x-empty-state
                                icon="feather-user-check"
                                title="Nothing needs your attention"
                                message="Locked-out customers asking for help will queue up here." />
                        </div>
                    @else
                        <div class="table-scroll">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Requester</th>
                                        <th>Type</th>
                                        <th>Received</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pendingRequests as $accountRequest)
                                        <tr>
                                            <td>
                                                <div class="cell-media">
                                                    <span class="avatar-initials sm">
                                                        {{ \Illuminate\Support\Str::of((string) $accountRequest->name)->substr(0, 2) }}
                                                    </span>
                                                    <div>
                                                        <p class="cell-title">{{ $accountRequest->name }}</p>
                                                        <p class="cell-sub">{{ $accountRequest->email }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $accountRequest->typeTone() }}">
                                                    <i class="{{ $accountRequest->typeIcon() }}"></i>
                                                    {{ $accountRequest->typeLabel() }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted fs-13"
                                                      title="{{ $accountRequest->created_at?->format('d M Y, h:i A') }}">
                                                    {{ $accountRequest->created_at?->diffForHumans() }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <div class="card-footer">
                        <a href="{{ route('admin.account-requests.index') }}" class="btn btn-soft btn-sm">
                            All requests <i class="feather-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        @endcan

        {{-- ---------- Low stock ---------- --}}
        @can('foods.view')
            <div class="col-xxl-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5>Low Stock</h5>
                        <span class="text-muted fs-13">{{ $lowStockFoods->count() }} items at or below alert</span>
                    </div>

                    @if ($lowStockFoods->isEmpty())
                        <div class="card-body">
                            <x-empty-state
                                icon="feather-check-circle"
                                title="Stock levels are healthy"
                                message="No active item has fallen to its low stock alert." />
                        </div>
                    @else
                        <div class="table-scroll">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th class="num">Left</th>
                                        <th class="num">Alert At</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($lowStockFoods as $food)
                                        <tr>
                                            <td>
                                                <div class="cell-media">
                                                    <span class="avatar-initials sm">
                                                        {{ \Illuminate\Support\Str::of((string) $food->name)->substr(0, 2) }}
                                                    </span>
                                                    <div>
                                                        <p class="cell-title">{{ $food->name }}</p>
                                                        <p class="cell-sub">{{ $food->sku ?: 'No SKU' }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="num">{{ number_format((float) $food->quantity) }}</td>
                                            <td class="num">{{ number_format((float) $food->low_stock_alert) }}</td>
                                            <td>
                                                @if ((float) $food->quantity <= 0)
                                                    <span class="status-pill off">Out of stock</span>
                                                @else
                                                    <span class="status-pill wait">Running low</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <div class="card-footer">
                        <a href="{{ route('admin.foods.index') }}" class="btn btn-soft btn-sm">
                            Manage foods <i class="feather-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        @endcan

    </div>
</div>
@endsection

@push('styles')
<style>
    /* Small pipeline tiles — every colour comes from a design token so the
       dark skin is handled by the same markup. */
    .dash-tiles {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(162px, 1fr));
        gap: 14px;
    }

    .dash-tile {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        border: 1px solid var(--ar-line);
        border-radius: var(--ar-radius-sm);
        background: var(--ar-surface-2);
        text-decoration: none;
        transition: border-color .2s ease, transform .2s ease, box-shadow .2s ease;
    }

    a.dash-tile:hover {
        border-color: var(--ar-primary);
        transform: translateY(-2px);
        box-shadow: var(--ar-shadow-xs);
    }

    .dash-tile-icon {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        background: var(--ar-primary-soft);
        color: var(--ar-primary);
    }

    .dash-tile.tone-success .dash-tile-icon { background: var(--ar-success-soft); color: var(--ar-success); }
    .dash-tile.tone-warning .dash-tile-icon { background: var(--ar-warning-soft); color: var(--ar-warning); }
    .dash-tile.tone-danger  .dash-tile-icon { background: var(--ar-danger-soft);  color: var(--ar-danger); }
    .dash-tile.tone-info    .dash-tile-icon { background: var(--ar-info-soft);    color: var(--ar-info); }

    .dash-tile-value {
        display: block;
        font-size: 19px;
        font-weight: 800;
        line-height: 1.1;
        color: var(--ar-ink);
        font-variant-numeric: tabular-nums;
    }

    .dash-tile-label {
        display: block;
        margin-top: 3px;
        font-size: 12px;
        font-weight: 600;
        color: var(--ar-muted);
    }

    .dash-chart { min-height: 330px; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var host = document.querySelector('#dashTrendChart');

    if (!host || typeof ApexCharts === 'undefined') {
        return;
    }

    var trend  = @json($trend);
    var labels = trend.map(function (d) { return d.label; });
    var money  = trend.map(function (d) { return Number(d.amount); });
    var counts = trend.map(function (d) { return Number(d.orders); });

    function token(name) {
        return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    }

    function bdt(value) {
        return 'BDT ' + Number(value).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    /*
     * Everything that depends on the skin lives in here, so the very same
     * object can be handed to updateOptions() when the theme is toggled and
     * nothing drifts out of step. Colours are read off the CSS custom
     * properties rather than repeated, so the chart follows the tokens.
     */
    function skin() {
        var dark  = document.documentElement.classList.contains('app-skin-dark');
        var muted = token('--ar-muted');
        var line  = token('--ar-line');
        var axis  = { style: { colors: muted, fontSize: '11px' } };

        return {
            theme:  { mode: dark ? 'dark' : 'light' },
            colors: [token('--ar-primary'), token('--ar-accent')],
            grid:   { borderColor: line, strokeDashArray: 4, padding: { left: 6, right: 6 } },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                fontSize: '12px',
                labels: { colors: muted },
                markers: { radius: 12 }
            },
            tooltip: {
                theme: dark ? 'dark' : 'light',
                shared: true,
                intersect: false,
                y: {
                    formatter: function (value, opts) {
                        if (opts && opts.seriesIndex === 1) {
                            return Math.round(value) + (Math.round(value) === 1 ? ' order' : ' orders');
                        }
                        return bdt(value);
                    }
                }
            },
            xaxis: {
                categories: labels,
                labels: { style: axis.style },
                axisBorder: { color: line },
                axisTicks: { color: line },
                tooltip: { enabled: false }
            },
            yaxis: [
                {
                    seriesName: 'Revenue',
                    labels: {
                        style: axis.style,
                        formatter: function (value) {
                            return Math.round(value).toLocaleString('en-US');
                        }
                    }
                },
                {
                    seriesName: 'Orders',
                    opposite: true,
                    min: 0,
                    labels: {
                        style: axis.style,
                        formatter: function (value) { return Math.round(value); }
                    }
                }
            ]
        };
    }

    var options = Object.assign({
        chart: {
            type: 'area',
            height: 330,
            background: 'transparent',
            fontFamily: 'inherit',
            toolbar: { show: false },
            zoom: { enabled: false }
        },
        series: [
            { name: 'Revenue', type: 'area', data: money },
            { name: 'Orders',  type: 'line', data: counts }
        ],
        stroke: { curve: 'smooth', width: [2.5, 2], dashArray: [0, 5] },
        fill: {
            type: ['gradient', 'solid'],
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.38,
                opacityTo: 0.02,
                stops: [0, 92, 100]
            }
        },
        dataLabels: { enabled: false },
        markers: { size: 0, hover: { size: 5 } }
    }, skin());

    var chart = new ApexCharts(host, options);

    chart.render().then(function () {
        document.addEventListener('ar:themechange', function () {
            chart.updateOptions(skin(), false, false);
        });
    });
})();
</script>
@endpush
