@extends('backend.master')

@section('content')
<div class="row justify-content-center">

    <div class="col-xl-6 col-lg-8 col-md-10">
        <div class="card">
            <div class="card-header">
                <h5>Edit Category</h5>
            </div>

            <div class="card-body">

                {{-- Flash Messages --}}
                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
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

                <form action="{{ route('admin.category.update', $category->id) }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            value="{{ old('name', $category->name) }}"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea
                            name="description"
                            class="form-control"
                            rows="3"
                        >{{ old('description', $category->description) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="1" {{ old('status', $category->status) == 1 ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="0" {{ old('status', $category->status) == 0 ? 'selected' : '' }}>
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
