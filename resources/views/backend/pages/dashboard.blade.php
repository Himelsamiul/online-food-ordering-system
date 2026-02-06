@extends('backend.master')

@section('content')

<div class="page-header mb-4">
    <h4 class="fw-bold">Admin Dashboard</h4>
    <small class="text-muted">System summary & operational insights</small>
</div>

<div class="container-fluid">
    <div class="row g-5">

        {{-- ================= USERS & ORDERS ================= --}}
        <div class="col-12">
            <h6 class="section-title text-primary">Users & Orders</h6>
        </div>

        @php
            $userOrderCards = [
                ['title'=>'Total Users','value'=>$totalUsers,'icon'=>'users','bg'=>'bg-blue'],
                ['title'=>'Total Orders','value'=>$totalOrders,'icon'=>'shopping-cart','bg'=>'bg-green'],
                ['title'=>'Pending Orders','value'=>$pendingOrders,'icon'=>'clock','bg'=>'bg-yellow'],
                ['title'=>'Cooking Orders','value'=>$cookingOrders,'icon'=>'coffee','bg'=>'bg-purple'],
                ['title'=>'Out for Delivery','value'=>$outForDeliveryOrders,'icon'=>'truck','bg'=>'bg-cyan'],
                ['title'=>'Delivered Orders','value'=>$deliveredOrders,'icon'=>'check-circle','bg'=>'bg-success'],
                ['title'=>'Cancelled Orders','value'=>$cancelledOrders,'icon'=>'x-circle','bg'=>'bg-red'],
            ];
        @endphp

        @foreach($userOrderCards as $card)
            <div class="col-xxl-3 col-xl-4 col-md-6">
                <div class="dash-card {{ $card['bg'] }}">
                    <div>
                        <small>{{ $card['title'] }}</small>
                        <h3>{{ number_format($card['value']) }}</h3>
                    </div>
                    <i class="feather-{{ $card['icon'] }}"></i>
                </div>
            </div>
        @endforeach

        {{-- ================= PAYMENTS ================= --}}
        <div class="col-12">
            <h6 class="section-title text-success">Payments Overview</h6>
        </div>

        @php
            $paymentCards = [
                ['title'=>'Total Revenue','value'=>$totalOrderAmount,'icon'=>'dollar-sign','bg'=>'bg-orange','money'=>true],
                ['title'=>'COD Orders','value'=>$codOrdersCount,'icon'=>'archive','bg'=>'bg-indigo'],
                ['title'=>'COD Total Amount','value'=>$codTotalAmount,'icon'=>'layers','bg'=>'bg-teal','money'=>true],
                ['title'=>'COD Collected','value'=>$codCollectedAmount,'icon'=>'check','bg'=>'bg-success','money'=>true],
                ['title'=>'COD Pending','value'=>$codPendingAmount,'icon'=>'alert-circle','bg'=>'bg-warning','money'=>true],
                ['title'=>'Stripe Orders','value'=>$stripeOrdersCount,'icon'=>'zap','bg'=>'bg-blue-dark'],
                ['title'=>'Stripe Amount','value'=>$stripeTotalAmount,'icon'=>'trending-up','bg'=>'bg-cyan-dark','money'=>true],
            ];
        @endphp

        @foreach($paymentCards as $card)
            <div class="col-xxl-3 col-xl-4 col-md-6">
                <div class="dash-card {{ $card['bg'] }}">
                    <div>
                        <small>{{ $card['title'] }}</small>
                        <h3>
                            {{ isset($card['money']) ? '৳ ' : '' }}{{ number_format($card['value']) }}
                        </h3>
                    </div>
                    <i class="feather-{{ $card['icon'] }}"></i>
                </div>
            </div>
        @endforeach

        {{-- ================= DELIVERY ================= --}}
        <div class="col-12">
            <h6 class="section-title text-info">Delivery Management</h6>
        </div>

        @php
            $deliveryCards = [
                ['title'=>'Delivery Men','value'=>$totalDeliveryMen,'icon'=>'user-check','bg'=>'bg-cyan'],
                ['title'=>'Delivery Runs','value'=>$totalDeliveryRuns,'icon'=>'map','bg'=>'bg-blue'],
                ['title'=>'Active Runs','value'=>$activeDeliveryRuns,'icon'=>'navigation','bg'=>'bg-purple'],
                ['title'=>'Completed Runs','value'=>$completedDeliveryRuns,'icon'=>'award','bg'=>'bg-success'],
            ];
        @endphp

        @foreach($deliveryCards as $card)
            <div class="col-xxl-3 col-xl-4 col-md-6">
                <div class="dash-card {{ $card['bg'] }}">
                    <div>
                        <small>{{ $card['title'] }}</small>
                        <h3>{{ number_format($card['value']) }}</h3>
                    </div>
                    <i class="feather-{{ $card['icon'] }}"></i>
                </div>
            </div>
        @endforeach

        {{-- ================= INVENTORY ================= --}}
        <div class="col-12">
            <h6 class="section-title text-warning">Inventory</h6>
        </div>

        @php
            $inventoryCards = [
                ['title'=>'Total Foods','value'=>$totalFoods,'icon'=>'grid','bg'=>'bg-teal'],
                ['title'=>'Subcategories','value'=>$totalSubcategories,'icon'=>'layers','bg'=>'bg-orange'],
                ['title'=>'Units','value'=>$totalUnits,'icon'=>'box','bg'=>'bg-indigo'],
            ];
        @endphp

        @foreach($inventoryCards as $card)
            <div class="col-xxl-3 col-xl-4 col-md-6">
                <div class="dash-card {{ $card['bg'] }}">
                    <div>
                        <small>{{ $card['title'] }}</small>
                        <h3>{{ number_format($card['value']) }}</h3>
                    </div>
                    <i class="feather-{{ $card['icon'] }}"></i>
                </div>
            </div>
        @endforeach

    </div>

    {{-- ================= PIE CHART SECTION ================= --}}
    <div class="row mt-5 g-4">

        {{-- LEFT : ORDER STATUS --}}
        <div class="col-xl-4 col-lg-5 col-md-6">
            <div class="chart-card">
                <h6 class="fw-bold text-center mb-2">Order Status</h6>
                <canvas id="orderStatusChart" height="190"></canvas>
            </div>
        </div>

        {{-- RIGHT : DELIVERY RUN STATUS --}}
        <div class="col-xl-4 col-lg-5 col-md-6 ms-auto">
            <div class="chart-card">
                <h6 class="fw-bold text-center mb-2">Delivery Run Status</h6>
                <canvas id="deliveryRunChart" height="190"></canvas>
            </div>
        </div>

    </div>
</div>

{{-- ================= STYLES ================= --}}
<style>
.section-title{
    font-weight:600;
    margin-bottom:12px;
}

.dash-card{
    border-radius:18px;
    padding:22px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    color:#fff;
    box-shadow:0 14px 32px rgba(0,0,0,.35);
    transition:.35s ease;
}
.dash-card:hover{
    transform:translateY(-8px) scale(1.02);
}
.dash-card h3{
    margin-top:6px;
    font-weight:800;
}
.dash-card small{
    opacity:.9;
}
.dash-card i{
    font-size:30px;
}

/* DEEP COLORS */
.bg-blue{background:linear-gradient(135deg,#1e3c72,#2a5298);}
.bg-blue-dark{background:linear-gradient(135deg,#0f2027,#203a43);}
.bg-green{background:linear-gradient(135deg,#11998e,#38ef7d);}
.bg-success{background:linear-gradient(135deg,#16a34a,#166534);}
.bg-yellow{background:linear-gradient(135deg,#f59e0b,#d97706);}
.bg-orange{background:linear-gradient(135deg,#f97316,#ea580c);}
.bg-red{background:linear-gradient(135deg,#ef4444,#b91c1c);}
.bg-purple{background:linear-gradient(135deg,#7c3aed,#a855f7);}
.bg-cyan{background:linear-gradient(135deg,#0ea5e9,#0284c7);}
.bg-cyan-dark{background:linear-gradient(135deg,#06beb6,#48b1bf);}
.bg-indigo{background:linear-gradient(135deg,#4f46e5,#6366f1);}
.bg-teal{background:linear-gradient(135deg,#134e5e,#71b280);}

.chart-card{
    background:#fff;
    border-radius:18px;
    padding:18px;
    box-shadow:0 14px 32px rgba(0,0,0,.25);
}
</style>

{{-- ================= CHART JS ================= --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
/* ORDER STATUS PIE */
new Chart(document.getElementById('orderStatusChart'), {
    type: 'pie',
    data: {
        labels: ['Pending','Cooking','Out for Delivery','Delivered','Cancelled'],
        datasets: [{
            data: [
                {{ $pendingOrders }},
                {{ $cookingOrders }},
                {{ $outForDeliveryOrders }},
                {{ $deliveredOrders }},
                {{ $cancelledOrders }}
            ],
            backgroundColor: [
                '#f59e0b',
                '#8b5cf6',
                '#0ea5e9',
                '#22c55e',
                '#ef4444'
            ],
        }]
    },
    options: {
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});

/* DELIVERY RUN STATUS PIE */
new Chart(document.getElementById('deliveryRunChart'), {
    type: 'pie',
    data: {
        labels: ['On The Way','Completed'],
        datasets: [{
            data: [
                {{ $activeDeliveryRuns }},
                {{ $completedDeliveryRuns }}
            ],
            backgroundColor: [
                '#0ea5e9',
                '#22c55e'
            ],
        }]
    },
    options: {
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});
</script>

@endsection
