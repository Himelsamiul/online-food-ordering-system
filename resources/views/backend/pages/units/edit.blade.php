@extends('backend.master')

@section('content')
<div class="row justify-content-center">

    <div class="col-xl-6 col-lg-8 col-md-10">
        <div class="card">
            <div class="card-header">
                <h5>Edit Unit</h5>
            </div>

            <div class="card-body">

                {{-- Error Message (duplicate unit etc.) --}}
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

                <form action="{{ route('admin.units.update', $unit->id) }}"
                      method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Unit Name --}}
                    <div class="mb-3">
                        <label class="form-label">Unit Name</label>
                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            value="{{ old('name', $unit->name) }}"
                            required
                        >
                    </div>

                    {{-- Status --}}
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="1" {{ old('status', $unit->status) == 1 ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="0" {{ old('status', $unit->status) == 0 ? 'selected' : '' }}>
                                Inactive
                            </option>
                        </select>
                    </div>

                    {{-- Buttons --}}
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            Update Unit
                        </button>

                        <a href="{{ route('admin.units.index') }}"
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
