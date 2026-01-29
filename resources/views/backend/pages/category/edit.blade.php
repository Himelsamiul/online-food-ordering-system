@extends('backend.master')

@section('content')
<div class="row justify-content-center">

    <div class="col-xl-6 col-lg-8 col-md-10">
        <div class="card">
            <div class="card-header">
                <h5>Edit Category</h5>
            </div>

            <div class="card-body">
                <form action="{{ route('admin.category.update', $category->id) }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            value="{{ $category->name }}"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea
                            name="description"
                            class="form-control"
                            rows="3"
                        >{{ $category->description }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="1" {{ $category->status ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="0" {{ !$category->status ? 'selected' : '' }}>
                                Inactive
                            </option>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            Update Category
                        </button>

                        <a href="{{ route('admin.category.index') }}"
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
