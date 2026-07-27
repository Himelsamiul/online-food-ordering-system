@extends('frontend.master')
@section('title', 'Forgot Password')

@section('content')

<section class="sf-auth-wrap">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="sf-auth-card">

                    <div class="sf-request-badge">
                        <i class="fa fa-envelope-o" aria-hidden="true"></i>
                    </div>

                    <h3>Forgot your password?</h3>
                    <p class="sf-auth-sub">
                        Enter the email on your account and we will send a 6-digit reset code.
                    </p>

                    <form action="{{ route('password.email') }}" method="POST">
                        @csrf

                        <div class="form-group mb-4">
                            <label for="email">Email Address</label>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   placeholder="example@mail.com"
                                   autocomplete="email"
                                   class="form-control @error('email') is-invalid @enderror">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-warning">Send Reset Code</button>
                    </form>

                    <div class="sf-help-box">
                        <p>
                            <strong>Code not arriving?</strong>
                            An administrator can generate a new password by hand and email it straight to you.
                        </p>
                        <a class="btn btn-warning"
                           href="{{ route('account.help', ['type' => 'password', 'email' => old('email')]) }}">
                            Ask an admin instead
                        </a>
                    </div>

                    <div class="sf-auth-foot">
                        <a href="{{ route('login') }}">Back to login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
