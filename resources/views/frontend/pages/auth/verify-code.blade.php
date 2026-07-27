@extends('frontend.master')
@section('title', 'Enter your code')

@section('content')

<section class="sf-auth-wrap">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="sf-auth-card">

                    <div class="sf-request-badge">
                        <i class="fa fa-shield" aria-hidden="true"></i>
                    </div>

                    <h3>Enter your code</h3>
                    <p class="sf-auth-sub">
                        We sent a {{ config('security.otp.length', 6) }}-digit code to
                        <strong style="color:#fff">{{ $email }}</strong>.
                        It expires in {{ $expiresMinutes }} minutes and can only be used once.
                    </p>

                    <form action="{{ route('password.verify.submit') }}" method="POST">
                        @csrf

                        <div class="form-group mb-4">
                            <label for="code">Verification code</label>
                            <input type="text"
                                   id="code"
                                   name="code"
                                   class="form-control sf-otp @error('code') is-invalid @enderror"
                                   inputmode="numeric"
                                   pattern="[0-9]*"
                                   maxlength="{{ config('security.otp.length', 6) }}"
                                   autocomplete="one-time-code"
                                   placeholder="{{ str_repeat('0', config('security.otp.length', 6)) }}"
                                   autofocus required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-warning">Verify code</button>
                    </form>

                    <form action="{{ route('password.resend') }}" method="POST" class="sf-auth-foot">
                        @csrf
                        Didn't get it?
                        <button type="submit" class="sf-btn-link">Send another code</button>
                    </form>

                    <div class="sf-auth-foot">
                        <a href="{{ route('password.request') }}" class="sf-link-muted">Use a different email</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
    // Digits only, and submit the moment the code is complete — this field is
    // the only thing on the page, so an extra click adds nothing.
    document.querySelectorAll('.sf-otp').forEach(function (input) {
        input.addEventListener('input', function () {
            input.value = input.value.replace(/\D/g, '');

            if (input.value.length === input.maxLength) {
                input.form.submit();
            }
        });
    });
</script>
@endpush
