@extends('backend.master')

@section('content')
<div class="row justify-content-center">

    <div class="col-xl-6 col-lg-8 col-md-10">
        <div class="card">
            <div class="card-header">
                <h5>Edit Subcategory</h5>
            </div>

            <div class="card-body">
                <form action="{{ route('admin.subcategory.update', $subcategory->id) }}"
                      method="POST">
                    @csrf

                    {{-- Category --}}
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-control" required>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ $subcategory->category_id == $category->id ? 'selected' : '' }}>
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
                            value="{{ $subcategory->name }}"
                            required
                        >
                    </div>

                    {{-- Status --}}
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="1" {{ $subcategory->status ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="0" {{ !$subcategory->status ? 'selected' : '' }}>
                                Inactive
                            </option>
                        </select>
                    </div>

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
