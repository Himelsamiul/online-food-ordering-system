@extends('frontend.master')

@section('content')

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">

            <div class="col-lg-5 col-md-7 col-sm-10">
                <div class="card glass-card shadow-lg p-4">

                    <h3 class="text-center mb-4 text-white">Edit Profile</h3>

                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- Full Name --}}
                        <div class="mb-3">
                            <label class="text-white">Full Name</label>
                            <input type="text"
                                   name="full_name"
                                   value="{{ old('full_name', $user->full_name) }}"
                                   placeholder="e.g. John Doe"
                                   class="form-control @error('full_name') is-invalid @enderror">
                            @error('full_name')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>

                        {{-- Username --}}
                        <div class="mb-3">
                            <label class="text-white">Username</label>
                            <input type="text"
                                   name="username"
                                   value="{{ old('username', $user->username) }}"
                                   placeholder="e.g. johndoe123"
                                   class="form-control @error('username') is-invalid @enderror" readonly>
                            @error('username')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>

                        {{-- Phone --}}
                        <div class="mb-3">
                            <label class="text-white">Phone Number</label>
                            <input type="text"
                                   name="phone"
                                   value="{{ old('phone', $user->phone) }}"
                                   placeholder="01XXXXXXXXX"
                                   class="form-control @error('phone') is-invalid @enderror">
                            @error('phone')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="mb-3">
                            <label class="text-white">Email Address</label>
                            <input type="email"
                                   name="email"
                                   value="{{ old('email', $user->email) }}"
                                   placeholder="example@mail.com"
                                   class="form-control @error('email') is-invalid @enderror" readonly>
                            @error('email')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>

                        {{-- DOB --}}
                        <div class="mb-3">
                            <label class="text-white">Date of Birth</label>
                            <input type="date"
                                   name="dob"
                                   value="{{ old('dob', $user->dob) }}"
                                   class="form-control @error('dob') is-invalid @enderror">
                            @error('dob')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>
{{-- Address --}}
<div class="mb-3">
    <label class="text-white">Address</label>
    <textarea name="address"
              rows="3"
              placeholder="Enter your full address"
              class="form-control @error('address') is-invalid @enderror">{{ old('address', $user->address ?? '') }}</textarea>

    @error('address')
        <strong class="text-danger">{{ $message }}</strong>
    @enderror
</div>

                       {{-- Image --}}
<div class="mb-4">
    <label class="text-white">Profile Image (optional)</label>

    <div class="d-flex align-items-center gap-3 mt-2">
        {{-- Previous Image Preview --}}
        @if($user->image && file_exists(public_path('storage/'.$user->image)))
            <img src="{{ asset('storage/'.$user->image) }}"
                 alt="Current Profile Image"
                 width="60"
                 height="60"
                 style="object-fit: cover; border-radius: 6px; border:1px solid rgba(255,255,255,0.4);">
        @endif

        {{-- File Input --}}
        <input type="file"
               name="image"
               class="form-control @error('image') is-invalid @enderror">
    </div>

<small style="color: rgba(28, 226, 84, 0.85); font-weight: bold;">
    Leave empty to keep current image
</small>


    @error('image')
        <strong class="text-danger d-block">{{ $message }}</strong>
    @enderror
</div>


                        {{-- Actions --}}
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                Update Profile
                            </button>
                            <a href="{{ route('profile') }}" class="btn btn-outline-light">
                                Cancel
                            </a>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</section>

@endsection
