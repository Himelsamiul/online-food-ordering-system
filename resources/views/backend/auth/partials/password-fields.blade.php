@php
    $minLength = config('security.password_reset.min_password_length', 8);
@endphp

<div class="mb-3">
    <label class="form-label" for="password">New password</label>
    <div class="pw-wrap">
        <input type="password" id="password" name="password"
               class="form-control @error('password') is-invalid @enderror"
               minlength="{{ $minLength }}" autocomplete="new-password" autofocus required>
        <button type="button" class="pw-eye" data-toggle-password="#password" aria-label="Show password">
            <i class="feather-eye"></i>
        </button>
    </div>
    @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    <div class="pw-meter" aria-hidden="true"><span></span></div>
    <small class="form-text">At least {{ $minLength }} characters. Mix letters, numbers and symbols.</small>
</div>

<div class="mb-4">
    <label class="form-label" for="password_confirmation">Confirm new password</label>
    <div class="pw-wrap">
        <input type="password" id="password_confirmation" name="password_confirmation"
               class="form-control" minlength="{{ $minLength }}" autocomplete="new-password" required>
        <button type="button" class="pw-eye" data-toggle-password="#password_confirmation"
                aria-label="Show password">
            <i class="feather-eye"></i>
        </button>
    </div>
    <small class="form-text pw-match" hidden>Both passwords must match.</small>
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
            btn.querySelector('i').className = showing ? 'feather-eye' : 'feather-eye-off';
        });
    });

    // Rough strength read-out. Advisory only — the real rule is server-side.
    (function () {
        var pw    = document.getElementById('password');
        var conf  = document.getElementById('password_confirmation');
        var meter = document.querySelector('.pw-meter');
        var match = document.querySelector('.pw-match');

        if (!pw || !meter) { return; }

        pw.addEventListener('input', function () {
            var v = pw.value;
            var score = 0;

            if (v.length >= 8)            { score++; }
            if (v.length >= 12)           { score++; }
            if (/[a-z]/.test(v) && /[A-Z]/.test(v)) { score++; }
            if (/\d/.test(v))             { score++; }
            if (/[^A-Za-z0-9]/.test(v))   { score++; }

            meter.dataset.score = v ? Math.min(score, 4) : '';
        });

        if (conf && match) {
            var check = function () {
                match.hidden = !conf.value || conf.value === pw.value;
            };

            conf.addEventListener('input', check);
            pw.addEventListener('input', check);
        }
    })();
</script>
@endpush
@endonce
