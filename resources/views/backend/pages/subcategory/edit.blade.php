@extends('backend.master')
@section('title', 'Edit Subcategory')

@section('content')
<div class="container-fluid">

    <x-page-header
        title="Edit Subcategory"
        subtitle="Move it to another category, rename it, or take it off the menu."
        icon="feather-layers"
        :breadcrumb="['Catalog' => null, 'Subcategories' => route('admin.subcategory.index'), $subcategory->name => null]">
        <a href="{{ route('admin.subcategory.index') }}" class="btn btn-soft">
            <i class="feather-arrow-left"></i> Back to list
        </a>
    </x-page-header>

    <form action="{{ route('admin.subcategory.update', $subcategory->id) }}"
          method="POST"
          enctype="multipart/form-data">
        @csrf

        <div class="row g-4">

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Subcategory Details</h5>
                    </div>
                    <div class="card-body">

                        <div class="mb-3">
                            <label class="form-label" for="subcategory-category">Category</label>
                            <select name="category_id" id="subcategory-category" class="form-select" required>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id', $subcategory->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if (!$categories->contains('id', $subcategory->category_id))
                                <small class="text-muted">
                                    The current parent category is inactive, so it is not in this list.
                                    Saving now moves this subcategory to the selected category.
                                </small>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="subcategory-name">Subcategory Name</label>
                            <input type="text"
                                   id="subcategory-name"
                                   name="name"
                                   class="form-control"
                                   value="{{ old('name', $subcategory->name) }}"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="subcategory-image">Change Image</label>
                            <input type="file" id="subcategory-image" name="image" class="form-control" accept="image/*">
                            <small class="text-muted">
                                Leave empty to keep the current image.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="subcategory-status">Status</label>
                            <select name="status" id="subcategory-status" class="form-select">
                                <option value="1" {{ old('status', $subcategory->status) == 1 ? 'selected' : '' }}>
                                    Active
                                </option>
                                <option value="0" {{ old('status', $subcategory->status) == 0 ? 'selected' : '' }}>
                                    Inactive
                                </option>
                            </select>
                        </div>

                        <hr class="hr-soft">

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="feather-save"></i> Update Subcategory
                            </button>

                            <a href="{{ route('admin.subcategory.index') }}" class="btn btn-soft">Cancel</a>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card taxonomy-aside">
                    <div class="card-header">
                        <h5>Current Image</h5>
                    </div>
                    <div class="card-body">

                        @if ($subcategory->image)
                            <img src="{{ asset('storage/' . $subcategory->image) }}"
                                 alt="{{ $subcategory->name }}"
                                 class="current-image">
                        @else
                            <x-empty-state icon="feather-image" title="No image uploaded"
                                           message="Pick a file on the left and it previews here after saving." />
                        @endif

                        <hr class="hr-soft">

                        <p class="section-title">Details</p>
                        <div class="kv-list">
                            <div class="kv-row">
                                <span class="kv-key">Status</span>
                                <span class="kv-val">
                                    <span class="status-pill {{ $subcategory->status ? 'on' : 'off' }}">
                                        {{ $subcategory->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </span>
                            </div>
                            <div class="kv-row">
                                <span class="kv-key">Parent Category</span>
                                <span class="kv-val">{{ $subcategory->category->name ?? 'N/A' }}</span>
                            </div>
                            <div class="kv-row">
                                <span class="kv-key">Food Items</span>
                                <span class="kv-val">{{ $subcategory->foods()->count() }}</span>
                            </div>
                            <div class="kv-row">
                                <span class="kv-key">Created</span>
                                <span class="kv-val">{{ $subcategory->created_at?->format('d M Y, h:i A') ?? '—' }}</span>
                            </div>
                            <div class="kv-row">
                                <span class="kv-key">Last updated</span>
                                <span class="kv-val">{{ $subcategory->updated_at?->format('d M Y, h:i A') ?? '—' }}</span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </form>

</div>
@endsection

@push('styles')
<style>
    .current-image {
        display: block;
        width: 100%;
        max-width: 260px;
        aspect-ratio: 1 / 1;
        object-fit: cover;
        border-radius: var(--ar-radius-sm);
        border: 1px solid var(--ar-line);
    }

    @media (min-width: 992px) {
        .taxonomy-aside {
            position: sticky;
            top: 92px;
        }
    }
</style>
@endpush
