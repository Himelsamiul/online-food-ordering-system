@extends('backend.master')

@section('content')

{{-- ================= Flatpickr CSS ================= --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">

{{-- ================= Filter Styles ================= --}}
<style>
    .filter-card {
        background: linear-gradient(135deg, rgba(59,130,246,.08), rgba(16,185,129,.08));
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

    .filter-card .form-control,
    .filter-card .form-select {
        border-radius: 10px;
        background: #fff;
        transition: all .25s ease;
    }

    .filter-card .form-control:focus,
    .filter-card .form-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 .15rem rgba(59,130,246,.25);
    }

    .date-picker {
        cursor: pointer;
        font-weight: 500;
    }

    .flatpickr-calendar {
        border-radius: 14px;
        box-shadow: 0 15px 35px rgba(0,0,0,.18);
    }
</style>

<div class="row">

    <!-- ================= Add Unit ================= -->
    <div class="col-xl-4 col-lg-5 col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Add Unit</h5>
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

                <form action="{{ route('admin.units.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Unit Name</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               placeholder="e.g. pcs, ml, bottle"
                               value="{{ old('name') }}"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        Add Unit
                    </button>
                </form>

            </div>
        </div>
    </div>

    <!-- ================= Unit List ================= -->
    <div class="col-xl-8 col-lg-7 col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Unit List</h5>
            </div>
            <div class="card-body">

                {{-- ================= FILTER SECTION ================= --}}
                <div class="filter-card">
                    <form method="GET" action="{{ route('admin.units.index') }}">
                        <div class="row g-3 align-items-end">

                            <div class="col-md-4">
                                <label class="filter-label">Unit Name</label>
                                <input type="text"
                                       name="name"
                                       value="{{ request('name') }}"
                                       class="form-control"
                                       placeholder="🔍 Search unit">
                            </div>

                            <div class="col-md-3">
                                <label class="filter-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All</option>
                                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="filter-label">From</label>
                                <input type="text"
                                       id="from_date"
                                       name="from_date"
                                       value="{{ request('from_date') }}"
                                       class="form-control date-picker"
                                       placeholder="From date">
                            </div>

                            <div class="col-md-2">
                                <label class="filter-label">To</label>
                                <input type="text"
                                       id="to_date"
                                       name="to_date"
                                       value="{{ request('to_date') }}"
                                       class="form-control date-picker"
                                       placeholder="To date">
                            </div>

                            <div class="col-md-12 d-flex gap-2 mt-2">
                                <button type="submit" class="btn btn-primary">
                                    Search
                                </button>

                                <a href="{{ route('admin.units.index') }}"
                                   class="btn btn-outline-secondary">
                                    Reset
                                </a>
                            </div>

                        </div>
                    </form>
                </div>

                {{-- ================= TABLE ================= --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                        <tr>
                            <th style="width:60px;">SL</th>
                            <th>Unit Name</th>
                            <th>Create Time</th>
                            <th style="width:120px;">Status</th>
                            <th style="width:160px;">Action</th>
                        </tr>
                        </thead>
                        <tbody>

                        @forelse($units as $key => $unit)
                            <tr>
                                <td>{{ $units->firstItem() + $key }}</td>
                                <td>{{ $unit->name }}</td>
                                <td>{{ $unit->created_at->format('d M Y h:i A') }}</td>
                                <td>
                                    <span class="badge {{ $unit->status ? 'bg-success' : 'bg-danger' }}">
                                        {{ $unit->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-grid gap-1">

                                        <a href="{{ route('admin.units.edit', $unit->id) }}"
                                           class="btn btn-sm btn-primary">
                                            Edit
                                        </a>

                                        @if($unit->foods_count > 0)

                                            <button type="button"
                                                    class="btn btn-sm btn-secondary"
                                                    onclick="toggleFoods({{ $unit->id }})">
                                                Delete
                                            </button>

                                            <small class="text-muted text-center">
                                                Used in food
                                            </small>

                                            <div id="foods-{{ $unit->id }}" style="display:none;">
                                                <ul class="mb-0 ps-3 text-muted">
                                                    @foreach($unit->foods as $food)
                                                        <li>{{ $food->name }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>

                                        @else

                                            <form action="{{ route('admin.units.delete', $unit->id) }}"
                                                  method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Are you sure?')">
                                                    Delete
                                                </button>
                                            </form>

                                        @endif

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    No units found
                                </td>
                            </tr>
                        @endforelse

                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex justify-content-end">
                    {{ $units->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>
    </div>

</div>

{{-- ================= Flatpickr JS ================= --}}
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    const fromPicker = flatpickr("#from_date", {
        dateFormat: "Y-m-d",
        maxDate: "today",
        onChange: function(_, dateStr) {
            toPicker.set("minDate", dateStr);
        }
    });

    const toPicker = flatpickr("#to_date", {
        dateFormat: "Y-m-d",
        maxDate: "today",
        onChange: function(_, dateStr) {
            fromPicker.set("maxDate", dateStr);
        }
    });

    function toggleFoods(id) {
        const el = document.getElementById('foods-' + id);
        el.style.display = (el.style.display === 'none') ? 'block' : 'none';
    }
</script>

@endsection
