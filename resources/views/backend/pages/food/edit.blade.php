@extends('backend.master')

@section('content')
<div class="row justify-content-center">
<div class="col-xl-9">

<div class="card">
    <div class="card-header">
        <h5>Edit Food</h5>
    </div>

    <div class="card-body">

        {{-- Validation Errors --}}
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
              action="{{ route('admin.foods.update', $food->id) }}"
              enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- ================= BASIC ================= --}}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Food Name</label>
                    <input name="name"
                           class="form-control"
                           value="{{ old('name', $food->name) }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label>SKU</label>
                    <input id="sku_preview"
                           class="form-control"
                           value="{{ $food->sku }}"
                           readonly>
                </div>
            </div>

            {{-- ================= CATEGORY ================= --}}
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Subcategory</label>
                    <select name="subcategory_id"
                            id="subcategory_id"
                            class="form-control">
                        @foreach($subcategories as $sub)
                            <option value="{{ $sub->id }}"
                                data-category="{{ $sub->category->name }}"
                                {{ $food->subcategory_id == $sub->id ? 'selected' : '' }}>
                                {{ $sub->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Category</label>
                    <input id="category_name"
                           class="form-control"
                           value="{{ $food->subcategory->category->name }}"
                           readonly>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Unit</label>
                    <select name="unit_id" class="form-control">
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}"
                                {{ $food->unit_id == $unit->id ? 'selected' : '' }}>
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- ================= PRICE & STOCK ================= --}}
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label>Price</label>
                    <input name="price"
                           class="form-control"
                           value="{{ $food->price }}">
                </div>

                <div class="col-md-3 mb-3">
                    <label>Discount</label>
                    <input name="discount"
                           class="form-control"
                           value="{{ $food->discount }}">
                </div>

                <div class="col-md-3 mb-3">
                    <label>Quantity</label>
                    <input name="quantity"
                           class="form-control"
                           value="{{ $food->quantity }}">
                </div>

                <div class="col-md-3 mb-3">
                    <label>Low Stock Alert</label>
                    <input name="low_stock_alert"
                           class="form-control"
                           value="{{ $food->low_stock_alert }}">
                </div>
            </div>

            {{-- ================= EXTRA ================= --}}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Barcode</label>
                    <input name="barcode"
                           class="form-control"
                           value="{{ $food->barcode }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="1" {{ $food->status ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ !$food->status ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label>Description</label>
                <textarea name="description"
                          class="form-control"
                          rows="3">{{ $food->description }}</textarea>
            </div>

            {{-- ================= IMAGE ================= --}}
            <div class="mb-3">
                <label>Food Image</label>

                @if($food->image)
                    <div class="mb-2">
                        <img src="{{ asset('storage/'.$food->image) }}"
                             style="height:80px;border-radius:6px">
                    </div>
                @endif

                <input type="file" name="image" class="form-control">
            </div>

            {{-- ================= ACTION ================= --}}
            <div class="d-flex gap-2">
                <button class="btn btn-primary">Update</button>
                <a href="{{ route('admin.foods.index') }}"
                   class="btn btn-secondary">Back</a>
            </div>

        </form>
    </div>
</div>

</div>
</div>

{{-- ================= JS (Auto Category + SKU regenerate) ================= --}}
<script>
document.getElementById('subcategory_id').addEventListener('change', function () {
    const option = this.options[this.selectedIndex];

    // category auto change
    document.getElementById('category_name').value =
        option.dataset.category || '';

    // SKU regenerate preview
    const prefix = option.text.substring(0,2).toUpperCase();
    const sku = prefix + '-' +
        Math.floor(100 + Math.random() * 900) + '-' +
        Math.floor(100 + Math.random() * 900);

    document.getElementById('sku_preview').value = sku;
});
</script>
@endsection
