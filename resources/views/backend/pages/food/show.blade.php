@extends('backend.master')

@section('content')

<style>
    /* ---------- Animation ---------- */
    .fade-in {
        animation: fadeInUp .6s ease forwards;
        opacity: 0;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ---------- Image ---------- */
    .food-image-box {
        max-height: 280px;
        overflow: hidden;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
        transition: transform .3s ease;
    }

    .food-image-box img {
        max-height: 260px;
        width: auto;
        object-fit: contain;
        transition: transform .3s ease;
    }

    .food-image-box:hover img {
        transform: scale(1.08);
    }

    /* ---------- Cards ---------- */
    .hover-card {
        transition: all .25s ease;
    }

    .hover-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0,0,0,.12);
    }

    /* ---------- Price Colors ---------- */
    .price-base { color: #2563eb; font-weight: 600; }
    .price-discount { color: #dc2626; }
    .price-final { color: #16a34a; font-weight: 700; font-size: 18px; }

</style>

<div class="card fade-in">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"> Food Details</h5>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.foods.index') }}"
               class="btn btn-outline-secondary btn-sm">
                ← Back
            </a>

            <a href="{{ route('admin.foods.edit', $food->id) }}"
               class="btn btn-primary btn-sm">
                Edit
            </a>
        </div>
    </div>

    <div class="card-body">

        {{-- ================= TOP ================= --}}
        <div class="row g-4 mb-4">

            <div class="col-md-4 fade-in">
                <div class="food-image-box border">
                    @if($food->image)
                        <img src="{{ asset('storage/'.$food->image) }}" alt="Food Image">
                    @else
                        <span class="text-muted">No Image</span>
                    @endif
                </div>
            </div>

            <div class="col-md-8 fade-in">
                <table class="table table-bordered align-middle mb-0">
                    <tr><th width="30%">Food</th><td>{{ $food->name }}</td></tr>
                    <tr><th>SKU</th><td>{{ $food->sku }}</td></tr>
                    <tr><th>Category</th><td>{{ $food->subcategory->category->name ?? 'N/A' }}</td></tr>
                    <tr><th>Subcategory</th><td>{{ $food->subcategory->name ?? 'N/A' }}</td></tr>
                    <tr><th>Unit</th><td>{{ $food->unit->name ?? '-' }}</td></tr>
                    <tr><th>Created At</th><td>{{ $food->created_at->format('d M Y h:i A') }}</td></tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <span class="badge {{ $food->status ? 'bg-success' : 'bg-danger' }}">
                                {{ $food->status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>

        </div>

        {{-- ================= PRICE + STOCK ================= --}}
        <div class="row g-4 mb-4">

            <div class="col-md-6 fade-in">
                <div class="card hover-card h-100">
                    <div class="card-header responsive">
                        <strong> Pricing</strong>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered mb-0">
                            <tr>
                                <th>Base Price</th>
                                <td class="price-base">{{ number_format($price,2) }} tk</td>
                            </tr>
                            <tr>
                                <th>Discount</th>
                                <td>{{ $discountPercent ? $discountPercent.'%' : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Discount Amount</th>
                                <td class="price-discount">
                                    {{ $discountAmount > 0 ? '-'.number_format($discountAmount,2).' tk' : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <th>Final Price</th>
                                <td class="price-final">
                                    {{ number_format($finalPrice,2) }} tk
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6 fade-in">
                <div class="card hover-card h-100">
                    <div class="card-header responsive">
                        <strong> Stock</strong>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered mb-0">
                            <tr><th>Quantity</th><td>{{ $food->quantity }}</td></tr>
                            <tr><th>Low Alert</th><td>{{ $food->low_stock_alert ?? '-' }}</td></tr>
                            <tr><th>Barcode</th><td>{{ $food->barcode ?? '-' }}</td></tr>
                            <tr><th>Created</th><td>{{ $food->created_at->format('d M Y h:i A') }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        {{-- ================= DESCRIPTION ================= --}}
<div class="card fade-in">
    <div class="card-header">
        <strong>Description</strong>
    </div>

    <div class="card-body">
        @if($food->description)
            <div class="table-responsive">
                <p class="mb-0">
                    {{ $food->description }}
                </p>
            </div>
        @else
            <span class="text-muted">No description provided.</span>
        @endif
    </div>
</div>

    </div>
</div>

{{-- ================= JS EFFECT ================= --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.fade-in').forEach((el, i) => {
            setTimeout(() => {
                el.style.opacity = 1;
            }, i * 120);
        });
    });
</script>

@endsection
