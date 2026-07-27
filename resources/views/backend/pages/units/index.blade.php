@extends('backend.master')
@section('title', 'Units')

@section('content')
<div class="container-fluid">

    @php
        $canCreate  = auth()->user()?->can('units.create') ?? false;
        $canEdit    = auth()->user()?->can('units.edit') ?? false;
        $canDelete  = auth()->user()?->can('units.delete') ?? false;
        $hasActions = $canEdit || $canDelete;
        $listCols   = $canCreate ? 'col-lg-8' : 'col-12';
        $colspan    = $hasActions ? 5 : 4;
    @endphp

    <x-page-header
        title="Units"
        subtitle="How each food item is measured — pieces, millilitres, plates. Names are stored in lower case."
        icon="feather-sliders"
        :breadcrumb="['Catalog' => null, 'Units' => null]">
        @can('foods.view')
            <a href="{{ route('admin.foods.index') }}" class="btn btn-soft">
                <i class="feather-shopping-bag"></i> Foods
            </a>
        @endcan
    </x-page-header>

    <div class="row g-4">

        {{-- ================= List ================= --}}
        <div class="{{ $listCols }}">

            <form method="GET" action="{{ route('admin.units.index') }}" class="filter-card">
                <div class="row g-3 align-items-end">

                    <div class="col-md-4">
                        <label class="filter-label" for="filter-name">Unit Name</label>
                        <input type="text" id="filter-name" name="name"
                               value="{{ request('name') }}"
                               class="form-control" placeholder="Search unit">
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

                    <div class="col-12 d-flex gap-2 align-items-center flex-wrap">
                        <button type="submit" class="btn btn-primary">
                            <i class="feather-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.units.index') }}" class="btn btn-soft">Reset</a>
                        <span class="text-muted fs-13">Set both dates to filter by a date range.</span>
                    </div>

                </div>
            </form>

            <div class="card">
                <div class="card-header">
                    <h5>All Units</h5>
                    <span class="text-muted fs-13">{{ $units->total() }} total</span>
                </div>

                <div class="table-scroll">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width:56px;">SL</th>
                                <th>Unit</th>
                                <th style="width:170px;">Created</th>
                                <th style="width:110px;">Status</th>
                                @if ($hasActions)
                                    <th style="width:110px;">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($units as $key => $unit)
                                <tr>
                                    <td>{{ $units->firstItem() + $key }}</td>

                                    <td>
                                        <div class="cell-media">
                                            <span class="avatar-initials sm">
                                                {{ mb_strtoupper(mb_substr($unit->name, 0, 2)) }}
                                            </span>
                                            <div>
                                                <p class="cell-title">{{ $unit->name }}</p>
                                                <p class="cell-sub">
                                                    {{ $unit->foods_count }}
                                                    {{ $unit->foods_count === 1 ? 'food item' : 'food items' }}
                                                </p>
                                            </div>
                                        </div>

                                        @can('units.delete')
                                            @if ($unit->foods_count > 0)
                                                <div id="unit-foods-{{ $unit->id }}" class="dep-panel" hidden>
                                                    <p class="dep-title">Already used by these foods</p>
                                                    <ul class="dep-items">
                                                        @foreach ($unit->foods as $food)
                                                            <li>{{ $food->name }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                        @endcan
                                    </td>

                                    <td>{{ $unit->created_at?->format('d M Y, h:i A') ?? '—' }}</td>

                                    <td>
                                        <span class="status-pill {{ $unit->status ? 'on' : 'off' }}">
                                            {{ $unit->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>

                                    @if ($hasActions)
                                        <td>
                                            <div class="action-group">
                                                @can('units.edit')
                                                    <a href="{{ route('admin.units.edit', $unit->id) }}"
                                                       class="btn btn-icon btn-soft-warning" title="Edit unit">
                                                        <i class="feather-edit-2"></i>
                                                    </a>
                                                @endcan

                                                @can('units.delete')
                                                    @if ($unit->foods_count > 0)
                                                        <button type="button"
                                                                class="btn btn-icon btn-soft"
                                                                data-ar-toggle="#unit-foods-{{ $unit->id }}"
                                                                title="In use by {{ $unit->foods_count }} food items — show them">
                                                            <i class="feather-lock"></i>
                                                        </button>
                                                    @else
                                                        <form action="{{ route('admin.units.delete', $unit->id) }}"
                                                              method="POST" class="delete-form"
                                                              data-confirm-title="Delete {{ $unit->name }}?"
                                                              data-confirm-text="The unit is removed from the catalogue. This cannot be undone."
                                                              data-confirm-button="Yes, delete it">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                    class="btn btn-icon btn-soft-danger" title="Delete unit">
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
                                        <x-empty-state icon="feather-sliders" title="No units match this filter"
                                                       message="Clear the filters, or add the first unit from the panel beside this list." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($units->hasPages())
                    <div class="table-foot">
                        <p class="foot-note">Showing {{ $units->firstItem() }}&ndash;{{ $units->lastItem() }}
                            of {{ $units->total() }}</p>
                        {{ $units->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>

        {{-- ================= Add new ================= --}}
        @can('units.create')
            <div class="col-lg-4">
                <div class="card taxonomy-aside">
                    <div class="card-header">
                        <h5>Add Unit</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.units.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label" for="unit-name">Unit Name</label>
                                <input type="text"
                                       id="unit-name"
                                       name="name"
                                       class="form-control"
                                       placeholder="e.g. pcs, ml, bottle"
                                       value="{{ old('name') }}"
                                       required>
                                <small class="text-muted">Saved in lower case, and each name is used once.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="unit-status">Status</label>
                                <select name="status" id="unit-status" class="form-select">
                                    <option value="1" {{ old('status') === '0' ? '' : 'selected' }}>Active</option>
                                    <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="feather-plus"></i> Add Unit
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
