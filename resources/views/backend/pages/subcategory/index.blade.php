@extends('backend.master')

@section('content')
<div class="row">

    <!-- ================= Add Subcategory ================= -->
    <div class="col-xl-4 col-lg-5 col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Add Subcategory</h5>
            </div>
            <div class="card-body">

                <form action="{{ route('admin.subcategory.store') }}" method="POST">
                    @csrf

                    {{-- Category --}}
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">
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
                            placeholder="Subcategory name"
                            required
                        >
                    </div>

                    {{-- Status --}}
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Add Subcategory
                    </button>
                </form>

            </div>
        </div>
    </div>

    <!-- ================= Subcategory List ================= -->
    <div class="col-xl-8 col-lg-7 col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Subcategory List</h5>
            </div>
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width:60px;">#</th>
                                <th>Category</th>
                                <th>Subcategory</th>
                                <th style="width:120px;">Status</th>
                                <th style="width:120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subcategories as $key => $subcategory)
                                <tr>
                                    <td>{{ $subcategories->firstItem() + $key }}</td>

                                    <td>
                                        {{ $subcategory->category->name ?? 'N/A' }}
                                    </td>

                                    <td>{{ $subcategory->name }}</td>

                                    <td>
                                        @if($subcategory->status)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>

                                    <td>
                                        <a href="{{ route('admin.subcategory.edit', $subcategory->id) }}"
                                           class="btn btn-sm btn-primary">
                                            Edit
                                        </a>

                                        <form action="{{ route('admin.subcategory.delete', $subcategory->id) }}"
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
                                        No subcategories found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3 d-flex justify-content-end">
                    {{ $subcategories->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>
    </div>

</div>
@endsection
