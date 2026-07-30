@extends('rider.layout')
@section('title', 'Sign in')

@section('content')
<div class="rider-auth">

    <div class="rider-auth-logo">
        <i class="fa fa-motorcycle" aria-hidden="true"></i>
    </div>

    <h1>Rider sign in</h1>
    <p class="rider-auth-sub">Use the username the office gave you.</p>

    @if ($errors->any())
        <div class="rider-flash is-error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('rider.login.submit') }}" class="rider-form">
        @csrf

        <label for="login">Username or email</label>
        <input type="text" name="login" id="login" value="{{ old('login') }}"
               autocomplete="username" autocapitalize="none" spellcheck="false" required autofocus>

        <label for="password">Password</label>
        <div class="rider-pw">
            <input type="password" name="password" id="password" autocomplete="current-password" required>
            <button type="button" class="rider-pw-eye" aria-label="Show password">
                <i class="fa fa-eye" aria-hidden="true"></i>
            </button>
        </div>

        <label class="rider-check">
            <input type="checkbox" name="remember" value="1">
            <span>Keep me signed in on this phone</span>
        </label>

        <button type="submit" class="rider-btn rider-btn-primary">Sign in</button>
    </form>

    <p class="rider-auth-help">
        Forgotten your password? The office can reset it for you — riders cannot reset it themselves.
    </p>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var eye = document.querySelector('.rider-pw-eye');
        var pw  = document.getElementById('password');

        if (!eye || !pw) return;

        eye.addEventListener('click', function () {
            var showing = pw.type === 'text';
            pw.type = showing ? 'password' : 'text';
            eye.querySelector('i').className = showing ? 'fa fa-eye' : 'fa fa-eye-slash';
            eye.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
        });
    })();
</script>
@endpush
