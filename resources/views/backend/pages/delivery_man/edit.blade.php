@extends('backend.master')
@section('title', 'Edit ' . $deliveryMan->name)

@section('content')
<div class="container-fluid">

    <x-page-header
        title="Edit Delivery Man"
        subtitle="Update the contact and identity details on file for {{ $deliveryMan->name }}."
        icon="feather-truck"
        :breadcrumb="['Delivery' => null, 'Delivery Men' => route('admin.delivery-men.index'), 'Edit' => null]">

        <a href="{{ route('admin.delivery-men.index') }}" class="btn btn-soft">
            <i class="feather-arrow-left"></i> Back to Delivery Men
        </a>
    </x-page-header>

    <form action="{{ route('admin.delivery-men.update', $deliveryMan->id) }}"
          method="POST"
          enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-3">

            {{-- ================= DETAILS ================= --}}
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Details</h5>
                        <span class="text-muted fs-13">Email, phone and NID must stay unique</span>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="filter-label" for="name">Name <span class="text-danger">*</span></label>
                                <input type="text" id="name" name="name" class="form-control"
                                       value="{{ old('name', $deliveryMan->name) }}" required>
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="filter-label" for="email">Email <span class="text-danger">*</span></label>
                                <input type="email" id="email" name="email" class="form-control"
                                       value="{{ old('email', $deliveryMan->email) }}" required>
                                @error('email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="filter-label" for="phone">Phone <span class="text-danger">*</span></label>
                                <input type="text" id="phone" name="phone" class="form-control"
                                       value="{{ old('phone', $deliveryMan->phone) }}"
                                       placeholder="01XXXXXXXXX" required>
                                @error('phone')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="filter-label" for="nid_number">NID Number <span class="text-danger">*</span></label>
                                <input type="text" id="nid_number" name="nid_number" class="form-control"
                                       value="{{ old('nid_number', $deliveryMan->nid_number) }}"
                                       placeholder="9 or 13 digits" required>
                                @error('nid_number')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="filter-label" for="address">Address <span class="text-danger">*</span></label>
                                <textarea id="address" name="address" class="form-control" rows="2"
                                          required>{{ old('address', $deliveryMan->address) }}</textarea>
                                @error('address')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="filter-label" for="note">Note</label>
                                <input type="text" id="note" name="note" class="form-control"
                                       value="{{ old('note', $deliveryMan->note) }}"
                                       placeholder="Shift, coverage area, anything worth remembering">
                                @error('note')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Rider portal access --}}
                            <div class="col-12">
                                <div class="rider-access-head">
                                    <i class="feather-smartphone"></i>
                                    Rider app access
                                    @if ($deliveryMan->canSignIn())
                                        <span class="ok">enabled</span>
                                    @else
                                        <span>not set up</span>
                                    @endif
                                </div>
                                <small class="text-muted d-block mb-2">
                                    They sign in at <code>{{ url('/rider') }}</code>.
                                    Leave the password blank to keep the current one.
                                    @if ($deliveryMan->last_login_at)
                                        Last signed in {{ $deliveryMan->last_login_at->diffForHumans() }}.
                                    @endif
                                </small>
                            </div>

                            <div class="col-md-4">
                                <label class="filter-label" for="username">Username</label>
                                <input type="text" id="username" name="username" class="form-control"
                                       value="{{ old('username', $deliveryMan->username) }}"
                                       autocomplete="off" placeholder="rahim_rider">
                                @error('username')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="filter-label" for="password">
                                    {{ $deliveryMan->password ? 'New password' : 'Set password' }}
                                </label>
                                <input type="password" id="password" name="password" class="form-control"
                                       autocomplete="new-password" placeholder="Leave blank to keep current">
                                @error('password')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="filter-label" for="password_confirmation">Confirm password</label>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                       class="form-control" autocomplete="new-password">
                            </div>

                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="action-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="feather-save"></i> Update Delivery Man
                            </button>

                            <a href="{{ route('admin.delivery-men.index') }}" class="btn btn-soft">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= PHOTO & STATUS ================= --}}
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header"><h5>Photo</h5></div>

                    <div class="card-body">
                        @if ($deliveryMan->photo)
                            <img src="{{ asset('storage/' . $deliveryMan->photo) }}"
                                 alt="{{ $deliveryMan->name }}"
                                 style="width:100%;max-width:180px;aspect-ratio:1;object-fit:cover;border-radius:var(--ar-radius);border:1px solid var(--ar-line)">
                        @else
                            <span class="avatar-initials" style="width:76px;height:76px;border-radius:18px;font-size:24px">
                                {{ mb_strtoupper(mb_substr(trim((string) $deliveryMan->name), 0, 2)) ?: '?' }}
                            </span>
                        @endif

                        <hr class="hr-soft">

                        <label class="filter-label" for="photo">Replace Photo</label>
                        <input type="file" id="photo" name="photo" class="form-control" accept="image/*">
                        <small class="text-muted">Leave empty to keep the current photo. JPG, PNG or WebP, up to 2 MB.</small>
                        @error('photo')
                            <br><small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header"><h5>Record</h5></div>

                    <div class="card-body">
                        <div class="kv-list">
                            <div class="kv-row">
                                <span class="kv-key">Status</span>
                                <span class="kv-val">
                                    <span class="status-pill {{ $deliveryMan->status ? 'on' : 'off' }}">
                                        {{ $deliveryMan->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </span>
                            </div>

                            <div class="kv-row">
                                <span class="kv-key">Registered</span>
                                <span class="kv-val">
                                    {{ $deliveryMan->created_at ? $deliveryMan->created_at->format('d M Y, h:i A') : '—' }}
                                </span>
                            </div>

                            <div class="kv-row">
                                <span class="kv-key">Last Updated</span>
                                <span class="kv-val">
                                    {{ $deliveryMan->updated_at ? $deliveryMan->updated_at->format('d M Y, h:i A') : '—' }}
                                </span>
                            </div>
                        </div>

                        <p class="text-muted fs-13 mb-0 mt-3">
                            Availability is switched from the list page.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </form>

</div>
@endsection
