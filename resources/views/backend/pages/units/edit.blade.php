@extends('backend.master')
@section('title', 'Edit Unit')

@section('content')
<div class="container-fluid">

    <x-page-header
        title="Edit Unit"
        subtitle="Rename this unit or take it out of circulation. Food items keep pointing at it either way."
        icon="feather-sliders"
        :breadcrumb="['Catalog' => null, 'Units' => route('admin.units.index'), $unit->name => null]">
        <a href="{{ route('admin.units.index') }}" class="btn btn-soft">
            <i class="feather-arrow-left"></i> Back to list
        </a>
    </x-page-header>

    <div class="row g-4">

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5>Unit Details</h5>
                </div>
                <div class="card-body">

                    <form action="{{ route('admin.units.update', $unit->id) }}"
                          method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label" for="unit-name">Unit Name</label>
                            <input type="text"
                                   id="unit-name"
                                   name="name"
                                   class="form-control"
                                   value="{{ old('name', $unit->name) }}"
                                   required>
                            <small class="text-muted">Saved in lower case, and each name is used once.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="unit-status">Status</label>
                            <select name="status" id="unit-status" class="form-select">
                                <option value="1" {{ old('status', $unit->status) == 1 ? 'selected' : '' }}>
                                    Active
                                </option>
                                <option value="0" {{ old('status', $unit->status) == 0 ? 'selected' : '' }}>
                                    Inactive
                                </option>
                            </select>
                        </div>

                        <hr class="hr-soft">

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="feather-save"></i> Update Unit
                            </button>

                            <a href="{{ route('admin.units.index') }}" class="btn btn-soft">Cancel</a>
                        </div>

                    </form>

                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card taxonomy-aside">
                <div class="card-header">
                    <h5>Usage</h5>
                </div>
                <div class="card-body">

                    @php $foodsCount = $unit->foods()->count(); @endphp

                    <p class="section-title">Details</p>
                    <div class="kv-list">
                        <div class="kv-row">
                            <span class="kv-key">Status</span>
                            <span class="kv-val">
                                <span class="status-pill {{ $unit->status ? 'on' : 'off' }}">
                                    {{ $unit->status ? 'Active' : 'Inactive' }}
                                </span>
                            </span>
                        </div>
                        <div class="kv-row">
                            <span class="kv-key">Food Items</span>
                            <span class="kv-val">{{ $foodsCount }}</span>
                        </div>
                        <div class="kv-row">
                            <span class="kv-key">Created</span>
                            <span class="kv-val">{{ $unit->created_at?->format('d M Y, h:i A') ?? '—' }}</span>
                        </div>
                        <div class="kv-row">
                            <span class="kv-key">Last updated</span>
                            <span class="kv-val">{{ $unit->updated_at?->format('d M Y, h:i A') ?? '—' }}</span>
                        </div>
                    </div>

                    <hr class="hr-soft">

                    <p class="unit-note">
                        @if ($foodsCount > 0)
                            This unit is in use, so it cannot be deleted. Renaming it changes the label
                            shown on every food item that uses it.
                        @else
                            Nothing uses this unit yet, so it can still be deleted from the list.
                        @endif
                    </p>

                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('styles')
<style>
    .unit-note {
        margin: 0;
        font-size: 13px;
        line-height: 1.6;
        color: var(--ar-muted);
    }

    @media (min-width: 992px) {
        .taxonomy-aside {
            position: sticky;
            top: 92px;
        }
    }
</style>
@endpush
