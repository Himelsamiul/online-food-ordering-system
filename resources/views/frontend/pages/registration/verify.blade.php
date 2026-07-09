@extends('frontend.master')

@section('content')

<section class="registration-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="glass-card shadow p-4">
                    <h3 class="text-center mb-2">Verify your email</h3>
                    <p class="text-center mb-4">
                        We sent a 6-digit code to <strong>{{ $email }}</strong>
                    </p>

                    <form action="{{ route('register.verify.submit') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label>Verification Code</label>
                            <input type="text"
                                   name="code"
                                   inputmode="numeric"
                                   maxlength="6"
                                   autocomplete="one-time-code"
                                   placeholder="Enter 6-digit code"
                                   class="form-control text-center @error('code') is-invalid @enderror"
                                   style="letter-spacing:6px; font-size:20px;">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-outline-light mt-2">Verify &amp; Create Account</button>
                        </div>
                    </form>

                    <form action="{{ route('register.resend') }}" method="POST" class="text-center mt-3">
                        @csrf
                        <button type="submit" class="btn btn-link text-light p-0">Didn’t get the code? Resend</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
