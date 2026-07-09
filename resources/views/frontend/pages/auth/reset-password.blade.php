@extends('frontend.master')

@section('content')

<section class="registration-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="glass-card shadow p-4">
                    <h3 class="text-center mb-2">Reset Password</h3>
                    <p class="text-center mb-4">
                        Enter the code sent to <strong>{{ $email }}</strong> and set a new password.
                    </p>

                    <form action="{{ route('password.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="email" value="{{ $email }}">

                        <div class="form-group mb-3">
                            <label>Reset Code</label>
                            <input type="text"
                                   name="code"
                                   inputmode="numeric"
                                   maxlength="6"
                                   placeholder="6-digit code"
                                   class="form-control text-center @error('code') is-invalid @enderror"
                                   style="letter-spacing:6px; font-size:20px;">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label>New Password</label>
                            <input type="password"
                                   name="password"
                                   placeholder="Enter new password"
                                   class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label>Confirm New Password</label>
                            <input type="password"
                                   name="password_confirmation"
                                   placeholder="Re-enter new password"
                                   class="form-control">
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-outline-light mt-2">Reset Password</button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('password.request') }}" class="text-light">Resend / change email</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
