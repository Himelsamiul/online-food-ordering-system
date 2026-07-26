@extends('backend.master')

@section('content')

<style>
    .filter-card {
        background: linear-gradient(135deg, rgba(236,72,153,.08), rgba(99,102,241,.08));
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
        background: #eef2ff;
        color: #4338ca;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 12px;
    }
</style>

<div class="row">

    <!-- ================= Add Banner ================= -->
    <div class="col-xl-4 col-lg-5 col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Add Promo Banner</h5>
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

                <form action="{{ route('admin.promotions.store') }}"
                      method="POST" enctype="multipart/form-data">
                    @csrf
                    @include('backend.pages.promotions._form', ['promotion' => null])

                    <button type="submit" class="btn btn-primary">Add Banner</button>
                </form>

            </div>
        </div>
    </div>

    <!-- ================= Banner List ================= -->
    <div class="col-xl-8 col-lg-7 col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Promo Banners</h5>
            </div>
            <div class="card-body">

                <div class="filter-card">
                    <form method="GET" action="{{ route('admin.promotions.index') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label class="filter-label">Title</label>
                                <input type="text" name="title" value="{{ request('title') }}"
                                       class="form-control" placeholder="🔍 Search title">
                            </div>

                            <div class="col-md-3">
                                <label class="filter-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All</option>
                                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <div class="col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Search</button>
                                <a href="{{ route('admin.promotions.index') }}" class="btn btn-outline-secondary">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                        <tr>
                            <th style="width:55px;">Order</th>
                            <th style="width:110px;">Image</th>
                            <th>Title</th>
                            <th>Coupon</th>
                            <th>Schedule</th>
                            <th style="width:100px;">Status</th>
                            <th style="width:130px;">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($promotions as $promotion)
                            <tr>
                                <td>{{ $promotion->sort_order }}</td>

                                <td>
                                    @if($promotion->image)
                                        <img src="{{ asset('storage/'.$promotion->image) }}"
                                             width="90" class="rounded">
                                    @else
                                        <i class="fa fa-image text-muted"></i>
                                    @endif
                                </td>

                                <td>
                                    <strong>{{ $promotion->title }}</strong>
                                    @if($promotion->subtitle)
                                        <small class="d-block text-muted">{{ $promotion->subtitle }}</small>
                                    @endif
                                </td>

                                <td>
                                    @if($promotion->coupon)
                                        <span class="coupon-code">{{ $promotion->coupon->code }}</span>
                                        <small class="d-block text-muted">{{ $promotion->coupon->offer_label }}</small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <td>
                                    <small class="d-block">
                                        {{ $promotion->starts_at ? $promotion->starts_at->format('d M Y') : 'now' }}
                                        &rarr;
                                        {{ $promotion->ends_at ? $promotion->ends_at->format('d M Y') : 'no end' }}
                                    </small>

                                    @if($promotion->ends_at && $promotion->ends_at->isPast())
                                        <span class="badge bg-warning text-dark">Finished</span>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge {{ $promotion->status ? 'bg-success' : 'bg-danger' }}">
                                        {{ $promotion->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>

                                <td>
                                    <div class="d-grid gap-1">
                                        <a href="{{ route('admin.promotions.edit', $promotion->id) }}"
                                           class="btn btn-sm btn-primary">Edit</a>

                                        <form action="{{ route('admin.promotions.delete', $promotion->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger w-100"
                                                    onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No promo banners yet</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex justify-content-end">
                    {{ $promotions->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>
    </div>

</div>

@endsection
