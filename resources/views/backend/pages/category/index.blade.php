@extends('backend.master')

@section('content')
<div class="row">

    <!-- ================= Add Category ================= -->
    <div class="col-xl-4 col-lg-5 col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Add Category</h5>
            </div>
            <div class="card-body">

                <form action="{{ route('admin.category.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            placeholder="Category name"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea
                            name="description"
                            class="form-control"
                            rows="3"
                            placeholder="Optional description"
                        ></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Add Category
                    </button>
                </form>

            </div>
        </div>
    </div>

    <!-- ================= Category List ================= -->
    <div class="col-xl-8 col-lg-7 col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Category List</h5>
            </div>
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width:60px;">#</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th style="width:120px;">Status</th>
                                <th style="width:120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $key => $category)
                                <tr>
                                    <td>{{ $categories->firstItem() + $key }}</td>
                                    <td>{{ $category->name }}</td>
                                    <td>{{ $category->description }}</td>
                                    <td>
                                        @if($category->status)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
    <a href="{{ route('admin.category.edit', $category->id) }}"
       class="btn btn-sm btn-primary">
        Edit
    </a>

    <form action="{{ route('admin.category.delete', $category->id) }}"
          method="POST"
          class="d-inline">
        @csrf
        @method('DELETE')

        <button type="submit"
                class="btn btn-sm btn-danger"
                onclick="return confirm('Are you sure?')">
            Delete
        </button>
    </form>
</td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        No categories found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3 d-flex justify-content-end">
                    {{ $categories->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>
    </div>

</div>
@endsection
