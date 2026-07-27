@extends('backend.master')
@section('title', 'Subcategories')

@section('content')
<div class="container-fluid">

    @php
        $canCreate  = auth()->user()?->can('subcategories.create') ?? false;
        $canEdit    = auth()->user()?->can('subcategories.edit') ?? false;
        $canDelete  = auth()->user()?->can('subcategories.delete') ?? false;
        $hasActions = $canEdit || $canDelete;
        $listCols   = $canCreate ? 'col-lg-8' : 'col-12';
        $colspan    = $hasActions ? 6 : 5;
    @endphp

    <x-page-header
        title="Subcategories"
        subtitle="The second level of the menu. Every food item belongs to one subcategory."
        icon="feather-layers"
        :breadcrumb="['Catalog' => null, 'Subcategories' => null]">
        @can('categories.view')
            <a href="{{ route('admin.category.index') }}" class="btn btn-soft">
                <i class="feather-grid"></i> Categories
            </a>
        @endcan
    </x-page-header>

    <div class="row g-4">

        {{-- ================= List ================= --}}
        <div class="{{ $listCols }}">

            <form method="GET" action="{{ route('admin.subcategory.index') }}" class="filter-card">
                <div class="row g-3 align-items-end">

                    <div class="col-md-4">
                        <label class="filter-label" for="filter-category">Category</label>
                        <select name="category_id" id="filter-category" class="form-select">
                            <option value="">All Categories</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="filter-label" for="filter-name">Subcategory Name</label>
                        <input type="text" id="filter-name" name="name"
                               value="{{ request('name') }}"
                               class="form-control" placeholder="Search subcategory">
                    </div>

                    <div class="col-md-4">
                        <label class="filter-label" for="filter-status">Status</label>
                        <select name="status" id="filter-status" class="form-select">
                            <option value="">All</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="filter-label" for="from_date">From Date</label>
                        <input type="date" id="from_date" name="from_date"
                               value="{{ request('from_date') }}" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="filter-label" for="to_date">To Date</label>
                        <input type="date" id="to_date" name="to_date"
                               value="{{ request('to_date') }}" class="form-control">
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="feather-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.subcategory.index') }}" class="btn btn-soft">Reset</a>
                    </div>

                </div>
            </form>

            <div class="card">
                <div class="card-header">
                    <h5>All Subcategories</h5>
                    <span class="text-muted fs-13">{{ $subcategories->total() }} total</span>
                </div>

                <div class="table-scroll">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width:56px;">SL</th>
                                <th>Subcategory</th>
                                <th style="width:90px;">Items</th>
                                <th style="width:170px;">Created</th>
                                <th style="width:110px;">Status</th>
                                @if ($hasActions)
                                    <th style="width:110px;">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($subcategories as $key => $subcategory)
                                <tr>
                                    <td>{{ $subcategories->firstItem() + $key }}</td>

                                    <td>
                                        <div class="cell-media">
                                            @if ($subcategory->image)
                                                <img src="{{ asset('storage/' . $subcategory->image) }}"
                                                     alt="{{ $subcategory->name }}"
                                                     width="42" height="42">
                                            @else
                                                <span class="avatar-initials">
                                                    {{ mb_strtoupper(mb_substr($subcategory->name, 0, 2)) }}
                                                </span>
                                            @endif
                                            <div>
                                                <p class="cell-title">{{ $subcategory->name }}</p>
                                                <p class="cell-sub">
                                                    {{ $subcategory->category->name ?? 'No category' }}
                                                </p>
                                            </div>
                                        </div>

                                        @can('subcategories.delete')
                                            @if ($subcategory->foods_count > 0)
                                                <div id="foods-{{ $subcategory->id }}" class="dep-panel" hidden>
                                                    <p class="dep-title">Already used by these foods</p>
                                                    <ul class="dep-items">
                                                        @foreach ($subcategory->foods as $food)
                                                            <li>{{ $food->name }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                        @endcan
                                    </td>

                                    <td>
                                        <span class="badge {{ $subcategory->foods_count > 0 ? 'bg-primary' : 'bg-secondary' }}">
                                            {{ $subcategory->foods_count }}
                                        </span>
                                    </td>

                                    <td>{{ $subcategory->created_at?->format('d M Y, h:i A') ?? '—' }}</td>

                                    <td>
                                        <span class="status-pill {{ $subcategory->status ? 'on' : 'off' }}">
                                            {{ $subcategory->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>

                                    @if ($hasActions)
                                        <td>
                                            <div class="action-group">
                                                @can('subcategories.edit')
                                                    <a href="{{ route('admin.subcategory.edit', $subcategory->id) }}"
                                                       class="btn btn-icon btn-soft-warning" title="Edit subcategory">
                                                        <i class="feather-edit-2"></i>
                                                    </a>
                                                @endcan

                                                @can('subcategories.delete')
                                                    @if ($subcategory->foods_count > 0)
                                                        <button type="button"
                                                                class="btn btn-icon btn-soft"
                                                                data-ar-toggle="#foods-{{ $subcategory->id }}"
                                                                title="In use by {{ $subcategory->foods_count }} food items — show them">
                                                            <i class="feather-lock"></i>
                                                        </button>
                                                    @else
                                                        <form action="{{ route('admin.subcategory.delete', $subcategory->id) }}"
                                                              method="POST" class="delete-form"
                                                              data-confirm-title="Delete {{ $subcategory->name }}?"
                                                              data-confirm-text="The subcategory and its image are removed. This cannot be undone."
                                                              data-confirm-button="Yes, delete it">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                    class="btn btn-icon btn-soft-danger" title="Delete subcategory">
                                                                <i class="feather-trash-2"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endcan
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $colspan }}">
                                        <x-empty-state icon="feather-layers" title="No subcategories match this filter"
                                                       message="Clear the filters, or add the first subcategory from the panel beside this list." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($subcategories->hasPages())
                    <div class="table-foot">
                        <p class="foot-note">Showing {{ $subcategories->firstItem() }}&ndash;{{ $subcategories->lastItem() }}
                            of {{ $subcategories->total() }}</p>
                        {{ $subcategories->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>

        {{-- ================= Add new ================= --}}
        @can('subcategories.create')
            <div class="col-lg-4">
                <div class="card taxonomy-aside">
                    <div class="card-header">
                        <h5>Add Subcategory</h5>
                    </div>
                    <div class="card-body">

                        @if ($categories->isEmpty())
                            <x-empty-state icon="feather-grid" title="No active categories"
                                           message="A subcategory needs a parent. Activate or create a category first.">
                                @can('categories.view')
                                    <a href="{{ route('admin.category.index') }}" class="btn btn-soft-primary">
                                        Go to Categories
                                    </a>
                                @endcan
                            </x-empty-state>
                        @endif

                        <form action="{{ route('admin.subcategory.store') }}"
                              method="POST"
                              enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label" for="subcategory-category">Category</label>
                                <select name="category_id" id="subcategory-category" class="form-select" required>
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="subcategory-name">Subcategory Name</label>
                                <input type="text"
                                       id="subcategory-name"
                                       name="name"
                                       class="form-control"
                                       placeholder="Subcategory name"
                                       value="{{ old('name') }}"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="subcategory-image">Subcategory Image</label>
                                <input type="file" id="subcategory-image" name="image" class="form-control" accept="image/*">
                                <small class="text-muted">
                                    JPG, PNG or WEBP up to 2 MB. Optional.
                                </small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="subcategory-status">Status</label>
                                <select name="status" id="subcategory-status" class="form-select">
                                    <option value="1" {{ old('status') === '0' ? '' : 'selected' }}>Active</option>
                                    <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="feather-plus"></i> Add Subcategory
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endcan

    </div>
</div>
@endsection

@push('styles')
<style>
    .dep-panel {
        margin-top: 8px;
        padding: 9px 11px;
        border: 1px solid var(--ar-line);
        border-radius: var(--ar-radius-xs);
        background: var(--ar-surface-2);
        max-width: 260px;
        text-align: left;
    }

    .dep-title {
        margin: 0 0 5px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: var(--ar-muted);
    }

    .dep-items {
        margin: 0;
        padding-left: 16px;
        font-size: 12.5px;
        color: var(--ar-ink-2);
    }

    @media (min-width: 992px) {
        .taxonomy-aside {
            position: sticky;
            top: 92px;
        }
    }
</style>
@endpush
