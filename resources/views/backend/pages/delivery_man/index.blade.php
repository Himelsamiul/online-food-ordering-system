@extends('backend.master')

@section('content')
<div class="container-fluid">

    {{-- ================= PAGE TITLE ================= --}}
    <div class="row mb-3">
        <div class="col">
            <h4 class="fw-bold">Delivery Men</h4>
        </div>
    </div>

    {{-- ================= CREATE FORM ================= --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header fw-bold">
            ➕ Add New Delivery Man
        </div>

        <div class="card-body">
            <form action="{{ route('admin.delivery-men.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Phone <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control" placeholder="01XXXXXXXXX" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">NID Number <span class="text-danger">*</span></label>
                        <input type="text" name="nid_number" class="form-control" placeholder="9 or 13 digits" required>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Address <span class="text-danger">*</span></label>
                        <textarea name="address" class="form-control" rows="2" required></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Photo <span class="text-danger">*</span></label>
                        <input type="file" name="photo" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Note</label>
                        <input type="text" name="note" class="form-control">
                    </div>

                </div>

                <div class="mt-3">
                    <button class="btn btn-primary px-4">
                        Create Delivery Man
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================= FILTER SECTION ================= --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header fw-bold bg-light">
            🔍 Filter Delivery Men
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('admin.delivery-men.index') }}">
                <div class="row g-3 align-items-end">

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Name</label>
                        <input type="text" name="name"
                               value="{{ request('name') }}"
                               class="form-control filter-input"
                               placeholder="Search by name">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="text" name="phone"
                               value="{{ request('phone') }}"
                               class="form-control filter-input"
                               placeholder="01XXXXXXXXX">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select filter-input">
                            <option value="">All</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">From Date</label>
                        <input type="text" name="from_date"
                               value="{{ request('from_date') }}"
                               class="form-control datepicker filter-input"
                               placeholder="YYYY-MM-DD">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">To Date</label>
                        <input type="text" name="to_date"
                               value="{{ request('to_date') }}"
                               class="form-control datepicker filter-input"
                               placeholder="YYYY-MM-DD">
                    </div>

                    <div class="col-md-12 text-end mt-3">
                        <button class="btn btn-primary px-4">
                            Search
                        </button>
                        <a href="{{ route('admin.delivery-men.index') }}"
                           class="btn btn-outline-secondary ms-2">
                            Reset
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- ================= LIST TABLE ================= --}}
    <div class="card shadow-sm">
        <div class="card-header fw-bold">
            Delivery Man List
        </div>

        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th>NID</th>
                        <th>Note</th>
                        <th>Status</th>
                        <th width="120">Action</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($deliveryMen as $key => $man)
                    <tr>
                        <td>{{ $deliveryMen->firstItem() + $key }}</td>
                        <td>
                            <img src="{{ asset('storage/'.$man->photo) }}"
                                 width="45" height="45"
                                 class="rounded-circle">
                        </td>
                        <td>{{ $man->name }}</td>
                        <td>{{ $man->phone }}</td>
                        <td>{{ $man->email }}</td>
                        <td>{{ $man->address }}</td>
                        <td>{{ $man->nid_number }}</td>
                        <td>{{ $man->note }}</td>
                        <td>
                            @if($man->status)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="d-flex gap-1">
                            <form action="{{ route('admin.delivery-men.status', $man->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-sm btn-warning">🔄</button>
                            </form>

                            <a href="{{ route('admin.delivery-men.edit', $man->id) }}"
                               class="btn btn-sm btn-info">✏️</a>

                            <form action="{{ route('admin.delivery-men.delete', $man->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Delete this delivery man?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">🗑</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted">
                            No delivery man found
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $deliveryMen->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    flatpickr(".datepicker", {
        dateFormat: "Y-m-d",
        allowInput: true
    });

    document.querySelectorAll('.filter-input').forEach(el => {
        el.addEventListener('focus', () => el.classList.add('shadow-sm'));
        el.addEventListener('blur', () => el.classList.remove('shadow-sm'));
    });
</script>
@endsection



