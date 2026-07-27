@php
    $minLength = config('security.password_reset.min_password_length', 8);
@endphp

<div class="form-group mb-3">
    <label for="password">New password <span class="sf-req">*</span></label>
    <div class="sf-password">
        <input type="password" id="password" name="password"
               class="form-control @error('password') is-invalid @enderror"
               minlength="{{ $minLength }}" autocomplete="new-password" required autofocus>
        <button type="button" class="sf-password-eye" data-toggle-password="#password"
                aria-label="Show password">
            <i class="fa fa-eye" aria-hidden="true"></i>
        </button>
    </div>
    @error('password')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
    <small class="sf-hint">At least {{ $minLength }} characters. Mix letters, numbers and symbols.</small>
</div>

<div class="form-group mb-4">
    <label for="password_confirmation">Confirm new password <span class="sf-req">*</span></label>
    <div class="sf-password">
        <input type="password" id="password_confirmation" name="password_confirmation"
               class="form-control" minlength="{{ $minLength }}" autocomplete="new-password" required>
        <button type="button" class="sf-password-eye" data-toggle-password="#password_confirmation"
                aria-label="Show password">
            <i class="fa fa-eye" aria-hidden="true"></i>
        </button>
    </div>
</div>

@once
@push('scripts')
<script>
    document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var field = document.querySelector(btn.getAttribute('data-toggle-password'));
            if (!field) { return; }

            var showing = field.type === 'text';
            field.type = showing ? 'password' : 'text';
            btn.querySelector('i').className = showing ? 'fa fa-eye' : 'fa fa-eye-slash';
        });
    });
</script>
@endpush
@endonce
