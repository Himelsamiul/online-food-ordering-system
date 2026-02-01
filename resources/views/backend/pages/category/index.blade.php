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

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('admin.category.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               placeholder="Category name"
                               value="{{ old('name') }}"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description"
                                  class="form-control"
                                  rows="3"
                                  placeholder="Optional description">{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <!-- ORIGINAL CREATE BUTTON -->
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
                                <th style="width:60px;">SL</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Create Time</th>
                                <th style="width:120px;">Status</th>
                                <th style="width:160px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($categories as $key => $category)

                            @php
                                $subs = \App\Models\SubCategory::where('category_id', $category->id)->get();
                            @endphp

                            <tr>
                                <td>{{ $categories->firstItem() + $key }}</td>
                                <td>{{ $category->name }}</td>
                                <td>{{ $category->description }}</td>
                                <td>{{ $category->created_at->format('d M Y h:i A') }}</td>
                                <td>
                                    @if($category->status)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>

                                <!-- ACTION COLUMN -->
                                <td>
                                    <div class="d-grid gap-1">

                                        <!-- EDIT (FULL WIDTH) -->
                                        <a href="{{ route('admin.category.edit', $category->id) }}"
                                           class="btn btn-sm btn-primary w-100">
                                            Edit
                                        </a>

                                        <!-- DELETE -->
                                        @if($subs->count() > 0)

                                            <button type="button"
                                                    class="btn btn-sm btn-secondary w-100"
                                                    onclick="toggleSubs({{ $category->id }})">
                                                Delete
                                            </button>

                                            <small class="text-muted text-center">
                                                Used in subcategory
                                            </small>

                                            <div id="subs-{{ $category->id }}" style="display:none;">
                                                <ul class="mb-0 ps-3 text-muted">
                                                    @foreach($subs as $sub)
                                                        <li>{{ $sub->name }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>

                                        @else

                                            <form action="{{ route('admin.category.delete', $category->id) }}"
                                                  method="POST"
                                                  class="w-100">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="btn btn-sm btn-danger w-100"
                                                        onclick="return confirm('Are you sure?')">
                                                    Delete
                                                </button>
                                            </form>

                                        @endif

                                    </div>
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

                <div class="mt-3 d-flex justify-content-end">
                    {{ $categories->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>
    </div>

</div>

<script>
    function toggleSubs(id) {
        const el = document.getElementById('subs-' + id);
        el.style.display = (el.style.display === 'none') ? 'block' : 'none';
    }
</script>
@endsection
