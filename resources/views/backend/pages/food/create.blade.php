@extends('backend.master')

@section('content')
<div class="row justify-content-center">
<div class="col-xl-8">

<div class="card">
    <div class="card-header"><h5>Add Food</h5></div>

    <div class="card-body">

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.foods.store') }}"
              enctype="multipart/form-data">
            @csrf

            <label>Food Name</label>
            <input name="name" class="form-control mb-2" value="{{ old('name') }}">

            <label>Subcategory</label>
            <select name="subcategory_id" id="subcategory_id"
                    class="form-control mb-2">
                <option value="">Select</option>
                @foreach($subcategories as $sub)
                    <option value="{{ $sub->id }}"
                            data-category="{{ $sub->category->name }}">
                        {{ $sub->name }}
                    </option>
                @endforeach
            </select>

            <label>Category</label>
            <input id="category_name" class="form-control mb-2" readonly>

            <label>SKU</label>
            <input id="sku_preview" class="form-control mb-2" readonly>

            <div class="row">
                <div class="col-md-4">
                    <label>Price</label>
                    <input name="price" class="form-control mb-2">
                </div>
                <div class="col-md-4">
                    <label>Discount</label>
                    <input name="discount" class="form-control mb-2">
                </div>
                <div class="col-md-4">
                    <label>Quantity</label>
                    <input name="quantity" class="form-control mb-2">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <label>Low Stock Alert</label>
                    <input name="low_stock_alert" class="form-control mb-2">
                </div>
                <div class="col-md-4">
                    <label>Unit</label>
                    <select name="unit_id" class="form-control mb-2">
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Status</label>
                    <select name="status" class="form-control mb-2">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>

            <label>Barcode</label>
            <input name="barcode" class="form-control mb-2">

            <label>Food Image</label>
            <input type="file" name="image" class="form-control mb-2">

            <label>Description</label>
            <textarea name="description" class="form-control mb-3"></textarea>

            <button class="btn btn-primary">Create</button>
            <a href="{{ route('admin.foods.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>

</div>
</div>

<script>
document.getElementById('subcategory_id').addEventListener('change',function(){
    const opt=this.options[this.selectedIndex];
    document.getElementById('category_name').value=opt.dataset.category||'';
    const p=opt.text.substring(0,2).toUpperCase();
    document.getElementById('sku_preview').value=
        p+'-'+Math.floor(100+Math.random()*900)+'-'+Math.floor(100+Math.random()*900);
});
</script>
@endsection
