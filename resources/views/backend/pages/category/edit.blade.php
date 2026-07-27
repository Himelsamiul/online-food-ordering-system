@extends('backend.master')
@section('title', 'Edit Category')

@section('content')
<div class="container-fluid">

    <x-page-header
        title="Edit Category"
        subtitle="Rename this category, swap its storefront image, or take it off the menu."
        icon="feather-grid"
        :breadcrumb="['Catalog' => null, 'Categories' => route('admin.category.index'), $category->name => null]">
        <a href="{{ route('admin.category.index') }}" class="btn btn-soft">
            <i class="feather-arrow-left"></i> Back to list
        </a>
    </x-page-header>

    <form action="{{ route('admin.category.update', $category->id) }}"
          method="POST"
          enctype="multipart/form-data">
        @csrf

        <div class="row g-4">

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Category Details</h5>
                    </div>
                    <div class="card-body">

                        <div class="mb-3">
                            <label class="form-label" for="category-name">Name</label>
                            <input type="text"
                                   id="category-name"
                                   name="name"
                                   class="form-control"
                                   value="{{ old('name', $category->name) }}"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="category-description">Description</label>
                            <textarea name="description"
                                      id="category-description"
                                      class="form-control"
                                      rows="4"
                                      placeholder="Optional description">{{ old('description', $category->description) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="category-image">Category Image</label>
                            <input type="file" id="category-image" name="image" class="form-control" accept="image/*">
                            <small class="text-muted">
                                Shown on the storefront category cards. Leave empty to keep the current image.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="category-status">Status</label>
                            <select name="status" id="category-status" class="form-select">
                                <option value="1" {{ old('status', $category->status) == 1 ? 'selected' : '' }}>
                                    Active
                                </option>
                                <option value="0" {{ old('status', $category->status) == 0 ? 'selected' : '' }}>
                                    Inactive
                                </option>
                            </select>
                        </div>

                        <hr class="hr-soft">

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="feather-save"></i> Update Category
                            </button>

                            <a href="{{ route('admin.category.index') }}" class="btn btn-soft">Cancel</a>
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

                        @if ($category->image)
                            <img src="{{ asset('storage/' . $category->image) }}"
                                 alt="{{ $category->name }}"
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
                                    <span class="status-pill {{ $category->status ? 'on' : 'off' }}">
                                        {{ $category->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </span>
                            </div>
                            <div class="kv-row">
                                <span class="kv-key">Subcategories</span>
                                <span class="kv-val">{{ $category->subcategories()->count() }}</span>
                            </div>
                            <div class="kv-row">
                                <span class="kv-key">Created</span>
                                <span class="kv-val">{{ $category->created_at?->format('d M Y, h:i A') ?? '—' }}</span>
                            </div>
                            <div class="kv-row">
                                <span class="kv-key">Last updated</span>
                                <span class="kv-val">{{ $category->updated_at?->format('d M Y, h:i A') ?? '—' }}</span>
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
