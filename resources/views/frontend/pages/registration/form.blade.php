@extends('frontend.master')

@section('content')

<section class="registration-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">

                <div class="card shadow p-4">
                    <h3 class="text-center mb-4">User Registration</h3>

                    <form action="{{ route('register.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- Full Name --}}
                        <div class="form-group mb-3">
                            <label>Full Name</label>
                            <input type="text"
                                   name="full_name"
                                   value="{{ old('full_name') }}"
                                   class="form-control @error('full_name') is-invalid @enderror">
                            @error('full_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Username --}}
                        <div class="form-group mb-3">
                            <label>Username</label>
                            <input type="text"
                                   name="username"
                                   value="{{ old('username') }}"
                                   class="form-control @error('username') is-invalid @enderror">
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Phone --}}
                        <div class="form-group mb-3">
                            <label>Phone Number</label>
                            <input type="text"
                                   name="phone"
                                   placeholder="01XXXXXXXXX"
                                   value="{{ old('phone') }}"
                                   class="form-control @error('phone') is-invalid @enderror">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="form-group mb-3">
                            <label>Email Address</label>
                            <input type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   class="form-control @error('email') is-invalid @enderror">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Date of Birth --}}
                        <div class="form-group mb-3">
                            <label>Date of Birth</label>
                            <input type="date"
                                   name="dob"
                                   value="{{ old('dob') }}"
                                   class="form-control @error('dob') is-invalid @enderror">
                            @error('dob')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div class="form-group mb-3">
                            <label>Password</label>
                            <input type="password"
                                   name="password"
                                   class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Confirm Password --}}
                        <div class="form-group mb-3">
                            <label>Confirm Password</label>
                            <input type="password"
                                   name="password_confirmation"
                                   class="form-control">
                        </div>

                        {{-- Image --}}
                        <div class="form-group mb-4">
                            <label>Profile Image (optional)</label>
                            <input type="file"
                                   name="image"
                                   class="form-control @error('image') is-invalid @enderror">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Submit --}}
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                Register
                            </button>
                        </div>

                    </form>

                </div>

            </div>
        </div>
    </div>
</section>

@endsection
