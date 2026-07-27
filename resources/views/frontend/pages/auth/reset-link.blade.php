@extends('frontend.master')
@section('title', 'Set your password')

@section('content')

<section class="sf-auth-wrap">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="sf-auth-card">

                    <div class="sf-request-badge">
                        <i class="fa fa-lock" aria-hidden="true"></i>
                    </div>

                    <h3>Set your password</h3>
                    <p class="sf-auth-sub">
                        An administrator approved your request. Choose a new password for
                        <strong style="color:#fff">{{ $email }}</strong>.
                    </p>

                    <form action="{{ route('password.reset.link.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="email" value="{{ $email }}">
                        <input type="hidden" name="token" value="{{ $token }}">

                        @include('frontend.pages.auth._password-fields')

                        <button type="submit" class="btn btn-warning">Save password</button>
                    </form>

                    <div class="sf-help-box">
                        <p>
                            This link works <strong>once</strong>. After you save, it stops working and
                            your old password no longer signs you in.
                        </p>
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
