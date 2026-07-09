@extends('frontend.master')

@section('content')

<section class="registration-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="glass-card shadow p-4">
                    <h3 class="text-center mb-2">Forgot Password</h3>
                    <p class="text-center mb-4">
                        Enter your email and we’ll send you a reset code.
                    </p>

                    <form action="{{ route('password.email') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label>Email Address</label>
                            <input type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   placeholder="example@mail.com"
                                   class="form-control @error('email') is-invalid @enderror">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-outline-light mt-2">Send Reset Code</button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('login') }}" class="text-light">Back to login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
