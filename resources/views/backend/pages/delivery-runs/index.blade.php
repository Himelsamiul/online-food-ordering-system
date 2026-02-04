@extends('backend.master')

@section('content')
<style>
/* ===== Premium Filter UI ===== */
.filter-card {
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 10px 25px rgba(0,0,0,.06);
    padding: 20px;
    margin-bottom: 20px;
}

.filter-card .form-label {
    font-size: 13px;
    font-weight: 600;
    color: #475569;
}

.filter-card .form-control,
.filter-card .form-select {
    border-radius: 10px;
    font-size: 14px;
    padding: 8px 12px;
}

.filter-actions {
    display: flex;
    gap: 10px;
}

.icon-btn {
    width: 34px;
    height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
}

.icon-btn.view { background:#e0f2fe; color:#0369a1; }
.icon-btn.edit { background:#e0e7ff; color:#4338ca; }
.icon-btn.complete { background:#dcfce7; color:#166534; }
.icon-btn.delete { background:#fee2e2; color:#b91c1c; }

.icon-btn:hover { transform: scale(1.12); }

.badge-way { background:#fef3c7; color:#92400e; }
.badge-done { background:#dcfce7; color:#166534; }
</style>

<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between mb-3">
        <h4 class="fw-bold">Delivery Runs</h4>
        <a href="{{ route('admin.delivery-runs.create') }}" class="btn btn-primary">
            + Create Delivery Run
        </a>
    </div>

    {{-- ================= FILTER BOX ================= --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('admin.delivery-runs.index') }}">
            <div class="row g-3 align-items-end">

                {{-- Delivery Man --}}
                <div class="col-md-3">
                    <label class="form-label">Delivery Man</label>
                    <select name="delivery_man_id" class="form-select">
                        <option value="">All</option>
                        @foreach($deliveryMen as $man)
                            <option value="{{ $man->id }}"
                                {{ request('delivery_man_id')==$man->id?'selected':'' }}>
                                {{ $man->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Phone --}}
                <div class="col-md-2">
                    <label class="form-label">Customer Phone</label>
                    <input type="text"
                           name="phone"
                           value="{{ request('phone') }}"
                           class="form-control"
                           placeholder="01XXXXXXXXX">
                </div>

                {{-- Status --}}
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="on_the_way" {{ request('status')=='on_the_way'?'selected':'' }}>
                            On The Way
                        </option>
                        <option value="completed" {{ request('status')=='completed'?'selected':'' }}>
                            Completed
                        </option>
                    </select>
                </div>

                {{-- From Date --}}
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="text"
                           name="from_date"
                           value="{{ request('from_date') }}"
                           class="form-control datepicker"
                           placeholder="Select date">
                </div>

                {{-- To Date --}}
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="text"
                           name="to_date"
                           value="{{ request('to_date') }}"
                           class="form-control datepicker"
                           placeholder="Select date">
                </div>

                {{-- Actions --}}
                <div class="col-md-1">
                    <div class="filter-actions">
                        <button class="btn btn-primary w-100" title="Search">
                            🔍
                        </button>
                        <a href="{{ route('admin.delivery-runs.index') }}"
                           class="btn btn-outline-secondary w-100"
                           title="Reset">
                            ↻
                        </a>
                    </div>
                </div>

            </div>
        </form>
    </div>

    {{-- ================= TABLE ================= --}}
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Delivery Man</th>
                        <th>Phone Number</th>
                        <th>Orders</th>
                        <th>Departed At</th>
                        <th>Delivered At</th>
                        <th>Status</th>
                        <th width="140">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($runs as $key => $run)
                    <tr>
                        <td>{{ $runs->firstItem() + $key }}</td>

                        <td>{{ $run->deliveryMan->name ?? 'N/A' }}</td>
                        <td>{{ $run->deliveryMan->phone ?? 'N/A' }}</td>

                        <td>{{ count($run->order_ids) }} Order(s)</td>

                        <td>{{ $run->departed_at?->format('d M Y, h:i A') ?? '-' }}</td>

                        <td>{{ $run->returned_at?->format('d M Y, h:i A') ?? '-' }}</td>

                        <td>
                            @if($run->status === 'on_the_way')
                                <span class="badge badge-way">On The Way</span>
                            @else
                                <span class="badge badge-done">Completed</span>
                            @endif
                        </td>

                        {{-- ACTION ICONS --}}
                        <td class="d-flex gap-2">

                            <a href="{{ route('admin.delivery-runs.show',$run->id) }}"
                               class="icon-btn view" title="View">
                                👁
                            </a>

                            <a href="{{ route('admin.delivery-runs.edit',$run->id) }}"
                               class="icon-btn edit" title="Edit">
                                ✏️
                            </a>

                            @if($run->status !== 'completed')
                                <form action="{{ route('admin.delivery-runs.complete',$run->id) }}"
                                      method="POST">
                                    @csrf @method('PATCH')
                                    <button class="icon-btn complete" title="Complete">
                                        ✔
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('admin.delivery-runs.delete',$run->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Delete this delivery run?')">
                                @csrf @method('DELETE')
                                <button class="icon-btn delete" title="Delete">
                                    🗑
                                </button>
                            </form>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            No delivery runs found
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            {{ $runs->links('pagination::bootstrap-5') }}
        </div>
    </div>

</div>

{{-- Flatpickr --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
flatpickr(".datepicker", {
    dateFormat: "Y-m-d",
    disableMobile: true
});
</script>
@endsection
