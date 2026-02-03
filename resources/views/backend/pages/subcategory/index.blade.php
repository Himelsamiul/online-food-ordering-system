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

    <!-- ================= Add Subcategory ================= -->
    <div class="col-xl-4 col-lg-5 col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Add Subcategory</h5>
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

                <form action="{{ route('admin.subcategory.store') }}"
                      method="POST"
                      enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Subcategory Name</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               placeholder="Subcategory name"
                               value="{{ old('name') }}"
                               required>
                    </div>

                    {{-- IMAGE FIELD --}}
                    <div class="mb-3">
                        <label class="form-label">Subcategory Image</label>
                        <input type="file"
                               name="image"
                               class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Add Subcategory
                    </button>
                </form>

            </div>
        </div>
    </div>

    <!-- ================= Subcategory List ================= -->
    <div class="col-xl-8 col-lg-7 col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Subcategory List</h5>
            </div>
            <div class="card-body">

                {{-- ================= FILTER SECTION ================= --}}
                <div class="filter-card">
                    <form method="GET" action="{{ route('admin.subcategory.index') }}">
                        <div class="row g-3 align-items-end">

                            <div class="col-md-3">
                                <label class="filter-label">Category</label>
                                <select name="category_id" class="form-select">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="filter-label">Subcategory Name</label>
                                <input type="text"
                                       name="name"
                                       value="{{ request('name') }}"
                                       class="form-control"
                                       placeholder="🔍 Search subcategory">
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

                                <a href="{{ route('admin.subcategory.index') }}"
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
                            <th style="width:80px;">Image</th>
                            <th>Category</th>
                            <th>Subcategory</th>
                            <th>Create Time</th>
                            <th style="width:120px;">Status</th>
                            <th style="width:140px;">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($subcategories as $key => $subcategory)
                            <tr>
                                <td>{{ $subcategories->firstItem() + $key }}</td>

                                {{-- IMAGE --}}
                                <td>
                                    @if($subcategory->image)
                                        <img src="{{ asset('storage/'.$subcategory->image) }}"
                                             width="50"
                                             class="rounded">
                                    @else
                                        <i class="fa fa-image text-muted"></i>
                                    @endif
                                </td>

                                <td>{{ $subcategory->category->name ?? 'N/A' }}</td>
                                <td>{{ $subcategory->name }}</td>
                                <td>{{ $subcategory->created_at->format('d M Y h:i A') }}</td>
                                <td>
                                    <span class="badge {{ $subcategory->status ? 'bg-success' : 'bg-danger' }}">
                                        {{ $subcategory->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>

                                <td>
                                    <div class="d-grid gap-1">

                                        <a href="{{ route('admin.subcategory.edit', $subcategory->id) }}"
                                           class="btn btn-sm btn-primary">
                                            Edit
                                        </a>

                                        @if($subcategory->foods_count > 0)

                                            <button type="button"
                                                    class="btn btn-sm btn-secondary"
                                                    onclick="toggleFoods({{ $subcategory->id }})">
                                                Delete
                                            </button>

                                            <small class="text-muted text-center">
                                                Already used in food
                                            </small>

                                            <div id="foods-{{ $subcategory->id }}" style="display:none;">
                                                <ul class="mb-0 ps-3 text-muted">
                                                    @foreach($subcategory->foods as $food)
                                                        <li>{{ $food->name }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>

                                        @else

                                            <form action="{{ route('admin.subcategory.delete', $subcategory->id) }}"
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
                                <td colspan="7" class="text-center text-muted">
                                    No subcategories found
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex justify-content-end">
                    {{ $subcategories->links('pagination::bootstrap-5') }}
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
