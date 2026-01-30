@extends('frontend.master')

@section('content')

<section class="registration-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">

                <div class="card shadow p-4">
                    <h3 class="text-center mb-4">Login</h3>

                    <form action="{{ route('login.submit') }}" method="POST">
                        @csrf

                        {{-- Username or Email --}}
                        <div class="form-group mb-3">
                            <label>Username or Email</label>
                            <input type="text"
                                   name="login"
                                   value="{{ old('login') }}"
                                   class="form-control @error('login') is-invalid @enderror"
                                   placeholder="Username or Email">
                            @error('login')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div class="form-group mb-4">
                            <label>Password</label>
                            <input type="password"
                                   name="password"
                                   class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                        <div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" name="remember" id="remember">
    <label class="form-check-label" for="remember">
        Remember Me
    </label>
</div>

                        {{-- Submit --}}
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                Login
                            </button>
                        </div>
                    </form>

                </div>

            </div>
        </div>
    </div>
</section>

{{-- 🔥 SAME FIX AS REGISTRATION --}}
<style>
/* FIX footer overlap on inner pages */
.registration-section {
    position: relative;
    z-index: 1;
    background: #f8f9fa;
    padding-bottom: 100px; /* footer space */
}

.footer_section {
    position: relative !important;
    margin-top: 0 !important;
}
</style>

@endsection
