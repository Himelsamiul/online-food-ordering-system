@extends('backend.master')

@section('content')
<div class="container-fluid">

    {{-- PAGE TITLE --}}
    <div class="row mb-3">
        <div class="col">
            <h4 class="fw-bold">Edit Delivery Man</h4>
        </div>
    </div>

    <div class="card">
        <div class="card-body">

            <form action="{{ route('admin.delivery-men.update', $deliveryMan->id) }}"
                  method="POST"
                  enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               value="{{ old('name', $deliveryMan->name) }}"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Email <span class="text-danger">*</span>
                        </label>
                        <input type="email"
                               name="email"
                               class="form-control"
                               value="{{ old('email', $deliveryMan->email) }}"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Phone <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="phone"
                               class="form-control"
                               value="{{ old('phone', $deliveryMan->phone) }}"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            NID Number <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="nid_number"
                               class="form-control"
                               value="{{ old('nid_number', $deliveryMan->nid_number) }}"
                               required>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold">
                            Address <span class="text-danger">*</span>
                        </label>
                        <textarea name="address"
                                  class="form-control"
                                  rows="2"
                                  required>{{ old('address', $deliveryMan->address) }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Photo
                        </label>
                        <input type="file"
                               name="photo"
                               class="form-control">

                        <small class="text-muted">
                            Leave empty to keep current photo
                        </small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Note
                        </label>
                        <input type="text"
                               name="note"
                               class="form-control"
                               value="{{ old('note', $deliveryMan->note) }}">
                    </div>

                </div>

                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary">
                        💾 Update Delivery Man
                    </button>

                    <a href="{{ route('admin.delivery-men.index') }}"
                       class="btn btn-secondary">
                        Cancel
                    </a>
                </div>

            </form>

        </div>
    </div>

</div>
@endsection
