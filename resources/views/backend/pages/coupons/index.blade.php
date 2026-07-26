@extends('backend.master')

@section('content')

<style>
    .filter-card {
        background: linear-gradient(135deg, rgba(99,102,241,0.08), rgba(16,185,129,0.08));
        border-radius: 14px;
        padding: 18px;
        margin-bottom: 18px;
    }

    .filter-label {
        font-size: 12px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 4px;
    }

    .coupon-code {
        font-family: monospace;
        font-weight: 700;
        letter-spacing: .5px;
        background: #eef2ff;
        color: #4338ca;
        padding: 3px 10px;
        border-radius: 6px;
    }
</style>

<div class="row">

    <!-- ================= Add Coupon ================= -->
    <div class="col-xl-4 col-lg-5 col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Add Coupon</h5>
            </div>
            <div class="card-body">

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.coupons.store') }}" method="POST">
                    @csrf
                    @include('backend.pages.coupons._form', ['coupon' => null])

                    <button type="submit" class="btn btn-primary">Add Coupon</button>
                </form>

            </div>
        </div>
    </div>

    <!-- ================= Coupon List ================= -->
    <div class="col-xl-8 col-lg-7 col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Coupon List</h5>
            </div>
            <div class="card-body">

                <div class="filter-card">
                    <form method="GET" action="{{ route('admin.coupons.index') }}">
                        <div class="row g-3 align-items-end">

                            <div class="col-md-4">
                                <label class="filter-label">Code</label>
                                <input type="text" name="code" value="{{ request('code') }}"
                                       class="form-control" placeholder="🔍 Search code">
                            </div>

                            <div class="col-md-3">
                                <label class="filter-label">Type</label>
                                <select name="type" class="form-select">
                                    <option value="">All</option>
                                    <option value="percent" {{ request('type') === 'percent' ? 'selected' : '' }}>Percentage</option>
                                    <option value="fixed"   {{ request('type') === 'fixed' ? 'selected' : '' }}>Fixed</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="filter-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All</option>
                                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <div class="col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Search</button>
                                <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">Reset</a>
                            </div>

                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                        <tr>
                            <th style="width:55px;">SL</th>
                            <th>Code</th>
                            <th>Discount</th>
                            <th>Conditions</th>
                            <th>Validity</th>
                            <th>Used</th>
                            <th style="width:100px;">Status</th>
                            <th style="width:130px;">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($coupons as $key => $coupon)
                            <tr>
                                <td>{{ $coupons->firstItem() + $key }}</td>

                                <td>
                                    <span class="coupon-code">{{ $coupon->code }}</span>
                                    @if($coupon->description)
                                        <small class="d-block text-muted">{{ $coupon->description }}</small>
                                    @endif
                                </td>

                                <td>
                                    <strong>{{ $coupon->offer_label }}</strong>
                                    @if($coupon->type === 'percent' && $coupon->max_discount)
                                        <small class="d-block text-muted">
                                            max ৳{{ number_format($coupon->max_discount, 0) }}
                                        </small>
                                    @endif
                                </td>

                                <td>
                                    @if($coupon->min_order_amount)
                                        <small class="d-block">min order ৳{{ number_format($coupon->min_order_amount, 0) }}</small>
                                    @endif
                                    <small class="d-block text-muted">
                                        {{ $coupon->per_user_limit ? $coupon->per_user_limit . '× per customer' : 'unlimited per customer' }}
                                    </small>
                                </td>

                                <td>
                                    <small class="d-block">
                                        {{ $coupon->starts_at ? $coupon->starts_at->format('d M Y') : 'now' }}
                                        &rarr;
                                        {{ $coupon->expires_at ? $coupon->expires_at->format('d M Y') : 'no expiry' }}
                                    </small>

                                    @if($coupon->expires_at && $coupon->expires_at->isPast())
                                        <span class="badge bg-warning text-dark">Expired</span>
                                    @endif
                                </td>

                                <td>
                                    {{ $coupon->used_count }}{{ $coupon->usage_limit ? ' / ' . $coupon->usage_limit : '' }}
                                </td>

                                <td>
                                    <span class="badge {{ $coupon->status ? 'bg-success' : 'bg-danger' }}">
                                        {{ $coupon->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>

                                <td>
                                    <div class="d-grid gap-1">
                                        <a href="{{ route('admin.coupons.edit', $coupon->id) }}"
                                           class="btn btn-sm btn-primary">Edit</a>

                                        @if($coupon->usages_count > 0)
                                            <button type="button" class="btn btn-sm btn-secondary" disabled>Delete</button>
                                            <small class="text-muted text-center">Already used</small>
                                        @else
                                            <form action="{{ route('admin.coupons.delete', $coupon->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger w-100"
                                                        onclick="return confirm('Are you sure?')">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No coupons yet</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex justify-content-end">
                    {{ $coupons->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>
    </div>

</div>

@endsection
