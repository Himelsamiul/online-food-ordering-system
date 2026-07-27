@extends('backend.auth.partials.shell')
@section('title', 'Enter your code')

@section('card')
    <div class="auth-badge"><i class="feather-shield"></i></div>

    <h4>Enter your code</h4>
    <p class="auth-sub">
        We sent a {{ config('security.otp.length', 6) }}-digit code to
        <strong class="text-ink">{{ $email }}</strong>. It expires in {{ $expiresMinutes }} minutes
        and can only be used once.
    </p>

    <form action="{{ route('admin.password.verify.submit') }}" method="POST" novalidate>
        @csrf

        <div class="mb-4">
            <label class="form-label" for="code">Verification code</label>
            <input type="text" id="code" name="code"
                   class="form-control otp-input @error('code') is-invalid @enderror"
                   inputmode="numeric" pattern="[0-9]*"
                   maxlength="{{ config('security.otp.length', 6) }}"
                   autocomplete="one-time-code" autofocus required
                   placeholder="{{ str_repeat('0', config('security.otp.length', 6)) }}">
            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn btn-primary">Verify code</button>
    </form>

    <form action="{{ route('admin.password.resend') }}" method="POST" class="auth-alt">
        @csrf
        <span class="text-muted">Didn't get it?</span>
        <button type="submit" class="btn-link-plain">Send another code</button>
    </form>

    <p class="auth-alt"><a href="{{ route('admin.password.request') }}">Use a different email</a></p>
@endsection

@push('scripts')
<script>
    // Numbers only, and submit as soon as the code is complete — the field is
    // the only thing on the page, so an extra click adds nothing.
    document.querySelectorAll('.otp-input').forEach(function (input) {
        input.addEventListener('input', function () {
            input.value = input.value.replace(/\D/g, '');

            if (input.value.length === input.maxLength) {
                input.form.requestSubmit ? input.form.requestSubmit() : input.form.submit();
            }
        });
    });
</script>
@endpush
