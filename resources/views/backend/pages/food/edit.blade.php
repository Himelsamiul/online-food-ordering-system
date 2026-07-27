@extends('backend.master')
@section('title', 'Edit Food')

@section('content')
<div class="container-fluid">

    <x-page-header
        title="Edit Food"
        subtitle="{{ $food->name }} &middot; {{ $food->sku }}"
        icon="feather-edit-3"
        :breadcrumb="['Catalog' => null, 'Foods' => route('admin.foods.index'), 'Edit' => null]">

        <a href="{{ route('admin.foods.show', $food->id) }}" class="btn btn-soft">
            <i class="feather-eye"></i> View Details
        </a>
        <a href="{{ route('admin.foods.index') }}" class="btn btn-soft">
            <i class="feather-arrow-left"></i> Back to Foods
        </a>
    </x-page-header>

    <form method="POST" action="{{ route('admin.foods.update', $food->id) }}"
          enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-3">

            {{-- ================= MAIN ================= --}}
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Item Details</h5>
                        <span class="text-muted fs-13">Shown to customers on the storefront</span>
                    </div>

                    <div class="card-body">

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label" for="name">Food Name</label>
                                <input type="text" name="name" id="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $food->name) }}">
                                @error('name')
                                    <p class="text-danger fs-13 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label" for="sku_preview">SKU <small>read only</small></label>
                                <input type="text" id="sku_preview" class="form-control"
                                       value="{{ $food->sku }}" readonly>
                            </div>
                        </div>

                        <div class="row g-3 mt-0">
                            <div class="col-md-4">
                                <label class="form-label" for="subcategory_id">Subcategory</label>
                                <select name="subcategory_id" id="subcategory_id"
                                        class="form-select @error('subcategory_id') is-invalid @enderror">
                                    @foreach ($subcategories as $sub)
                                        <option value="{{ $sub->id }}"
                                                data-category="{{ $sub->category->name }}"
                                                {{ old('subcategory_id', $food->subcategory_id) == $sub->id ? 'selected' : '' }}>
                                            {{ $sub->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('subcategory_id')
                                    <p class="text-danger fs-13 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label" for="category_name">Category</label>
                                <input type="text" id="category_name" class="form-control" readonly
                                       value="{{ $food->subcategory->category->name ?? '' }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label" for="unit_id">Unit</label>
                                <select name="unit_id" id="unit_id"
                                        class="form-select @error('unit_id') is-invalid @enderror">
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}"
                                            {{ old('unit_id', $food->unit_id) == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('unit_id')
                                    <p class="text-danger fs-13 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <hr class="hr-soft">
                        <p class="section-title">Pricing</p>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="price">Price (BDT)</label>
                                <input type="number" step="0.01" min="1" name="price" id="price"
                                       class="form-control @error('price') is-invalid @enderror"
                                       value="{{ old('price', $food->price) }}">
                                @error('price')
                                    <p class="text-danger fs-13 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="discount">
                                    Discount (%) <small>0&ndash;100, leave empty for none</small>
                                </label>
                                <input type="number" step="0.01" min="0" max="100" name="discount" id="discount"
                                       class="form-control @error('discount') is-invalid @enderror"
                                       value="{{ old('discount', $food->discount) }}">
                                @error('discount')
                                    <p class="text-danger fs-13 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <hr class="hr-soft">
                        <p class="section-title">Stock</p>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="quantity">Quantity</label>
                                <input type="number" min="0" name="quantity" id="quantity"
                                       class="form-control @error('quantity') is-invalid @enderror"
                                       value="{{ old('quantity', $food->quantity) }}">
                                @error('quantity')
                                    <p class="text-danger fs-13 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label" for="low_stock_alert">
                                    Low Stock Alert <small>cannot exceed quantity</small>
                                </label>
                                <input type="number" min="0" name="low_stock_alert" id="low_stock_alert"
                                       class="form-control @error('low_stock_alert') is-invalid @enderror"
                                       value="{{ old('low_stock_alert', $food->low_stock_alert) }}">
                                @error('low_stock_alert')
                                    <p class="text-danger fs-13 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label" for="barcode">Barcode <small>optional</small></label>
                                <input type="text" name="barcode" id="barcode"
                                       class="form-control @error('barcode') is-invalid @enderror"
                                       value="{{ old('barcode', $food->barcode) }}">
                                @error('barcode')
                                    <p class="text-danger fs-13 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <hr class="hr-soft">

                        <div>
                            <label class="form-label" for="description">Description <small>optional</small></label>
                            <textarea name="description" id="description" rows="4"
                                      class="form-control @error('description') is-invalid @enderror">{{ old('description', $food->description) }}</textarea>
                            @error('description')
                                <p class="text-danger fs-13 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>

            {{-- ================= SIDEBAR ================= --}}
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Image &amp; Placement</h5>
                    </div>

                    <div class="card-body">

                        <div class="mb-3">
                            <label class="form-label" for="image">
                                Food Image <small>leave empty to keep the current one</small>
                            </label>

                            @if ($food->image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $food->image) }}"
                                         alt="{{ $food->name }}"
                                         style="width:100%;max-height:180px;object-fit:cover;border-radius:var(--ar-radius-sm)">
                                </div>
                            @endif

                            <input type="file" name="image" id="image" accept="image/*"
                                   class="form-control @error('image') is-invalid @enderror">
                            @error('image')
                                <p class="text-danger fs-13 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <hr class="hr-soft">

                        <div class="mb-3">
                            <label class="form-label" for="status">Status</label>
                            <select name="status" id="status"
                                    class="form-select @error('status') is-invalid @enderror">
                                <option value="1" {{ old('status', (string) $food->status) === '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status', (string) $food->status) === '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <p class="text-danger fs-13 mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-muted fs-13 mt-1">Inactive items stay in the catalogue but leave the storefront.</p>
                        </div>

                        <hr class="hr-soft">
                        <p class="section-title">Storefront Highlights</p>

                        <div class="form-check mb-3">
                            {{-- unchecked boxes never reach the request, so send an explicit 0 --}}
                            <input type="hidden" name="is_featured" value="0">
                            <input class="form-check-input" type="checkbox"
                                   name="is_featured" value="1" id="isFeatured"
                                   {{ old('is_featured', $food->is_featured) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isFeatured">
                                Featured Item
                                <small class="d-block text-muted">Shows in the "Featured" row on the home page</small>
                            </label>
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="is_popular" value="0">
                            <input class="form-check-input" type="checkbox"
                                   name="is_popular" value="1" id="isPopular"
                                   {{ old('is_popular', $food->is_popular) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isPopular">
                                Most Popular
                                <small class="d-block text-muted">Shows in the "Most Popular" row on the home page</small>
                            </label>
                        </div>

                    </div>

                    @can('foods.edit')
                        <div class="card-footer d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="feather-check"></i> Update Food
                            </button>
                            <a href="{{ route('admin.foods.index') }}" class="btn btn-soft">Cancel</a>
                        </div>
                    @endcan
                </div>
            </div>

        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
document.getElementById('subcategory_id').addEventListener('change', function () {
    const option = this.options[this.selectedIndex];

    // category follows the subcategory
    document.getElementById('category_name').value = option.dataset.category || '';

    // SKU preview only — the stored SKU is not changed on update
    const prefix = option.text.trim().substring(0, 2).toUpperCase();
    document.getElementById('sku_preview').value =
        prefix + '-' +
        Math.floor(100 + Math.random() * 900) + '-' +
        Math.floor(100 + Math.random() * 900);
});
</script>
@endpush
