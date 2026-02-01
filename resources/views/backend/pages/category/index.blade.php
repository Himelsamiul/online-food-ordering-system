@extends('backend.master')

@section('content')

{{-- ================= Flatpickr CSS ================= --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">

{{-- ================= Custom Filter Styles ================= --}}
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

    .filter-card .form-control,
    .filter-card .form-select {
        border-radius: 10px;
        transition: all 0.25s ease;
        background: #fff;
    }

    .filter-card .form-control:focus,
    .filter-card .form-select:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 .15rem rgba(99,102,241,.25);
    }

    .date-picker {
        cursor: pointer;
        font-weight: 500;
    }

    .flatpickr-calendar {
        border-radius: 14px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.18);
    }

    .btn-filter {
        border-radius: 10px;
        padding: 8px 18px;
        font-weight: 600;
    }

    .btn-reset {
        border-radius: 10px;
        padding: 8px 18px;
    }
</style>

<div class="row">

    <!-- ================= Add Category ================= -->
    <div class="col-xl-4 col-lg-5 col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Add Category</h5>
            </div>
            <div class="card-body">

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form action="{{ route('admin.category.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control"
                               placeholder="Category name"
                               value="{{ old('name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control"
                                  rows="3"
                                  placeholder="Optional description">{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Add Category
                    </button>
                </form>

            </div>
        </div>
    </div>

    <!-- ================= Category List ================= -->
    <div class="col-xl-8 col-lg-7 col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Category List</h5>
            </div>
            <div class="card-body">

                {{-- ================= FILTER SECTION ================= --}}
                <div class="filter-card">
                    <form method="GET" action="{{ route('admin.category.index') }}">
                        <div class="row g-3 align-items-end">

                            <div class="col-md-3">
                                <label class="filter-label">Category Name</label>
                                <input type="text"
                                       name="name"
                                       value="{{ request('name') }}"
                                       class="form-control"
                                       placeholder="🔍 Search name">
                            </div>

                            <div class="col-md-2">
                                <label class="filter-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All</option>
                                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="filter-label">From Date</label>
                                <input type="text"
                                       id="from_date"
                                       name="from_date"
                                       value="{{ request('from_date') }}"
                                       class="form-control date-picker"
                                       placeholder="Select date">
                            </div>

                            <div class="col-md-2">
                                <label class="filter-label">To Date</label>
                                <input type="text"
                                       id="to_date"
                                       name="to_date"
                                       value="{{ request('to_date') }}"
                                       class="form-control date-picker"
                                       placeholder="Select date">
                            </div>

                            <div class="col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-filter">
                                    🔎 Search
                                </button>

                                <a href="{{ route('admin.category.index') }}"
                                   class="btn btn-outline-secondary btn-reset">
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
                            <th>Name</th>
                            <th>Description</th>
                            <th>Create Time</th>
                            <th style="width:120px;">Status</th>
                            <th style="width:160px;">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($categories as $key => $category)

                            @php
                                $subs = \App\Models\SubCategory::where('category_id', $category->id)->get();
                            @endphp

                            <tr>
                                <td>{{ $categories->firstItem() + $key }}</td>
                                <td>{{ $category->name }}</td>
                                <td>{{ $category->description }}</td>
                                <td>{{ $category->created_at->format('d M Y h:i A') }}</td>
                                <td>
                                    <span class="badge {{ $category->status ? 'bg-success' : 'bg-danger' }}">
                                        {{ $category->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-grid gap-1">
                                        <a href="{{ route('admin.category.edit', $category->id) }}"
                                           class="btn btn-sm btn-primary w-100">
                                            Edit
                                        </a>

                                        @if($subs->count() > 0)
                                            <button type="button"
                                                    class="btn btn-sm btn-secondary w-100"
                                                    onclick="toggleSubs({{ $category->id }})">
                                                Delete
                                            </button>
                                            <small class="text-muted text-center">Used in subcategory</small>
                                            <div id="subs-{{ $category->id }}" style="display:none;">
                                                <ul class="mb-0 ps-3 text-muted">
                                                    @foreach($subs as $sub)
                                                        <li>{{ $sub->name }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @else
                                            <form action="{{ route('admin.category.delete', $category->id) }}"
                                                  method="POST" class="w-100">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger w-100"
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
                                <td colspan="6" class="text-center text-muted">
                                    No categories found
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex justify-content-end">
                    {{ $categories->links('pagination::bootstrap-5') }}
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
        allowInput: true,
        onChange: function (selectedDates, dateStr) {
            toPicker.set("minDate", dateStr);
        }
    });

    const toPicker = flatpickr("#to_date", {
        dateFormat: "Y-m-d",
        maxDate: "today",
        allowInput: true,
        onChange: function (selectedDates, dateStr) {
            fromPicker.set("maxDate", dateStr);
        }
    });

    function toggleSubs(id) {
        const el = document.getElementById('subs-' + id);
        el.style.display = (el.style.display === 'none') ? 'block' : 'none';
    }
</script>
@endsection
