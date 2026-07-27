@extends('backend.master')
@section('title', 'Categories')

@section('content')
<div class="container-fluid">

    @php
        $canCreate  = auth()->user()?->can('categories.create') ?? false;
        $canEdit    = auth()->user()?->can('categories.edit') ?? false;
        $canDelete  = auth()->user()?->can('categories.delete') ?? false;
        $hasActions = $canEdit || $canDelete;
        $listCols   = $canCreate ? 'col-lg-8' : 'col-12';
        $colspan    = $hasActions ? 6 : 5;
    @endphp

    <x-page-header
        title="Categories"
        subtitle="The top level of the menu. Subcategories and food items hang off these."
        icon="feather-grid"
        :breadcrumb="['Catalog' => null, 'Categories' => null]">
        @can('subcategories.view')
            <a href="{{ route('admin.subcategory.index') }}" class="btn btn-soft">
                <i class="feather-layers"></i> Subcategories
            </a>
        @endcan
    </x-page-header>

    <div class="row g-4">

        {{-- ================= List ================= --}}
        <div class="{{ $listCols }}">

            <form method="GET" action="{{ route('admin.category.index') }}" class="filter-card">
                <div class="row g-3 align-items-end">

                    <div class="col-md-4">
                        <label class="filter-label" for="filter-name">Category Name</label>
                        <input type="text" id="filter-name" name="name"
                               value="{{ request('name') }}"
                               class="form-control" placeholder="Search name">
                    </div>

                    <div class="col-md-3">
                        <label class="filter-label" for="filter-status">Status</label>
                        <select name="status" id="filter-status" class="form-select">
                            <option value="">All</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="filter-label" for="from_date">From Date</label>
                        <input type="date" id="from_date" name="from_date"
                               value="{{ request('from_date') }}" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="filter-label" for="to_date">To Date</label>
                        <input type="date" id="to_date" name="to_date"
                               value="{{ request('to_date') }}" class="form-control">
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="feather-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.category.index') }}" class="btn btn-soft">Reset</a>
                    </div>

                </div>
            </form>

            <div class="card">
                <div class="card-header">
                    <h5>All Categories</h5>
                    <span class="text-muted fs-13">{{ $categories->total() }} total</span>
                </div>

                <div class="table-scroll">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width:56px;">SL</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th style="width:170px;">Created</th>
                                <th style="width:110px;">Status</th>
                                @if ($hasActions)
                                    <th style="width:110px;">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categories as $key => $category)

                                @php
                                    $subs = \App\Models\Subcategory::where('category_id', $category->id)->get();
                                @endphp

                                <tr>
                                    <td>{{ $categories->firstItem() + $key }}</td>

                                    <td>
                                        <div class="cell-media">
                                            @if ($category->image)
                                                <img src="{{ asset('storage/' . $category->image) }}"
                                                     alt="{{ $category->name }}"
                                                     width="42" height="42">
                                            @else
                                                <span class="avatar-initials">
                                                    {{ mb_strtoupper(mb_substr($category->name, 0, 2)) }}
                                                </span>
                                            @endif
                                            <div>
                                                <p class="cell-title">{{ $category->name }}</p>
                                                <p class="cell-sub">
                                                    {{ $subs->count() }}
                                                    {{ $subs->count() === 1 ? 'subcategory' : 'subcategories' }}
                                                </p>
                                            </div>
                                        </div>

                                        @can('categories.delete')
                                            @if ($subs->count() > 0)
                                                <div id="subs-{{ $category->id }}" class="dep-panel" hidden>
                                                    <p class="dep-title">Delete these subcategories first</p>
                                                    <ul class="dep-items">
                                                        @foreach ($subs as $sub)
                                                            <li>{{ $sub->name }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                        @endcan
                                    </td>

                                    <td>
                                        @if (filled($category->description))
                                            <span class="cell-desc" title="{{ $category->description }}">
                                                {{ \Illuminate\Support\Str::limit($category->description, 80) }}
                                            </span>
                                        @else
                                            <span class="text-muted">&mdash;</span>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $category->created_at?->format('d M Y, h:i A') ?? '—' }}
                                    </td>

                                    <td>
                                        <span class="status-pill {{ $category->status ? 'on' : 'off' }}">
                                            {{ $category->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>

                                    @if ($hasActions)
                                        <td>
                                            <div class="action-group">
                                                @can('categories.edit')
                                                    <a href="{{ route('admin.category.edit', $category->id) }}"
                                                       class="btn btn-icon btn-soft-warning" title="Edit category">
                                                        <i class="feather-edit-2"></i>
                                                    </a>
                                                @endcan

                                                @can('categories.delete')
                                                    @if ($subs->count() > 0)
                                                        <button type="button"
                                                                class="btn btn-icon btn-soft"
                                                                data-ar-toggle="#subs-{{ $category->id }}"
                                                                title="In use by {{ $subs->count() }} subcategories — show them">
                                                            <i class="feather-lock"></i>
                                                        </button>
                                                    @else
                                                        <form action="{{ route('admin.category.delete', $category->id) }}"
                                                              method="POST" class="delete-form"
                                                              data-confirm-title="Delete {{ $category->name }}?"
                                                              data-confirm-text="The category and its image are removed. This cannot be undone."
                                                              data-confirm-button="Yes, delete it">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                    class="btn btn-icon btn-soft-danger" title="Delete category">
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
                                        <x-empty-state icon="feather-grid" title="No categories match this filter"
                                                       message="Clear the filters, or add the first category from the panel beside this list." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($categories->hasPages())
                    <div class="table-foot">
                        <p class="foot-note">Showing {{ $categories->firstItem() }}&ndash;{{ $categories->lastItem() }}
                            of {{ $categories->total() }}</p>
                        {{ $categories->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>

        {{-- ================= Add new ================= --}}
        @can('categories.create')
            <div class="col-lg-4">
                <div class="card taxonomy-aside">
                    <div class="card-header">
                        <h5>Add Category</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.category.store') }}"
                              method="POST"
                              enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label" for="category-name">Name</label>
                                <input type="text" id="category-name" name="name" class="form-control"
                                       placeholder="Category name"
                                       value="{{ old('name') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="category-description">Description</label>
                                <textarea name="description" id="category-description" class="form-control"
                                          rows="3"
                                          placeholder="Optional description">{{ old('description') }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="category-image">Category Image</label>
                                <input type="file" id="category-image" name="image" class="form-control" accept="image/*">
                                <small class="text-muted">
                                    Shown on the storefront category cards. JPG, PNG or WEBP up to 2 MB.
                                </small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="category-status">Status</label>
                                <select name="status" id="category-status" class="form-select">
                                    <option value="1" {{ old('status') === '0' ? '' : 'selected' }}>Active</option>
                                    <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="feather-plus"></i> Add Category
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
    .cell-desc {
        display: block;
        max-width: 34ch;
        font-size: 13px;
        color: var(--ar-muted);
    }

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
