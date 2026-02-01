@extends('backend.master')

@section('content')
<div class="row justify-content-center">

    <div class="col-xl-6 col-lg-8 col-md-10">
        <div class="card">
            <div class="card-header">
                <h5>Edit Subcategory</h5>
            </div>

            <div class="card-body">

                {{-- Error Message (duplicate etc.) --}}
                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Validation Errors --}}
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.subcategory.update', $subcategory->id) }}"
                      method="POST">
                    @csrf

                    {{-- Category --}}
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-control" required>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('category_id', $subcategory->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Subcategory Name --}}
                    <div class="mb-3">
                        <label class="form-label">Subcategory Name</label>
                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            value="{{ old('name', $subcategory->name) }}"
                            required
                        >
                    </div>

                    {{-- Status --}}
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="1" {{ old('status', $subcategory->status) == 1 ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="0" {{ old('status', $subcategory->status) == 0 ? 'selected' : '' }}>
                                Inactive
                            </option>
                        </select>
                    </div>

                    {{-- Buttons (UNCHANGED UI) --}}
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            Update Subcategory
                        </button>

                        <a href="{{ route('admin.subcategory.index') }}"
                           class="btn btn-secondary">
                            Back
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>

</div>
@endsection
