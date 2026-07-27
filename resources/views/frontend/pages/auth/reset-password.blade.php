@extends('frontend.master')
@section('title', 'Set a New Password')

@section('content')

{{--
    Step 3 of the self-service reset. The code was already proven at step 2, so
    this page only collects the new password — the controller re-checks that the
    verified code is still live before saving, and burns it afterwards.
--}}
<section class="sf-auth-wrap">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="sf-auth-card">

                    <div class="sf-request-badge">
                        <i class="fa fa-lock" aria-hidden="true"></i>
                    </div>

                    <h3>Choose a new password</h3>
                    <p class="sf-auth-sub">
                        Setting a new password for
                        <strong style="color:#fff">{{ $email }}</strong>.
                    </p>

                    <form action="{{ route('password.update') }}" method="POST">
                        @csrf

                        @include('frontend.pages.auth._password-fields')

                        <button type="submit" class="btn btn-warning">Save new password</button>
                    </form>

                    <div class="sf-auth-foot">
                        <a href="{{ route('password.verify') }}" class="sf-link-muted">Back to the code</a>
                        <div class="mt-2">
                            <a href="{{ route('login') }}">Cancel and sign in</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
