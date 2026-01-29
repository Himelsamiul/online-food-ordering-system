<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">

    <style>
        body {
            min-height: 100vh;
            background:
                linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.55)),
                url('{{ asset('admin2.avif') }}') center / cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, BlinkMacSystemFont;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: rgba(255, 255, 255, 0.10);
            backdrop-filter: blur(12px);
            border-radius: 14px;
            padding: 32px;
            box-shadow: 0 25px 45px rgba(0,0,0,0.45);
            color: #fff;
        }

        .login-card h4 {
            font-weight: 600;
        }

        .login-card small {
            color: #cbd5f5;
        }

        .form-floating > .form-control {
            background: transparent;
            color: #fff;
            border: 1px solid rgba(255,255,255,0.25);
        }

        .form-floating > .form-control:focus {
            background: transparent;
            border-color: #38bdf8;
            box-shadow: none;
        }

        .form-floating label {
            color: #cbd5f5;
        }

        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #cbd5f5;
            user-select: none;
        }

        .btn-login {
            background: #38bdf8;
            border: none;
            font-weight: 600;
        }

        .btn-login:hover {
            background: #0ea5e9;
        }

        .login-error {
            color: #f87171;
            font-size: 14px;
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <h4>Admin Panel</h4>
        <small>Secure access required</small>
    </div>

    <form method="POST" action="{{ route('admin.login.submit') }}">
        @csrf

        <div class="form-floating mb-3">
            <input type="email"
                   name="email"
                   class="form-control"
                   id="email"
                   placeholder="Email"
                   required>
            <label for="email">Email Address</label>
        </div>

        <div class="form-floating mb-3 position-relative">
            <input type="password"
                   name="password"
                   class="form-control"
                   id="password"
                   placeholder="Password"
                   required>
            <label for="password">Password</label>
            <span class="toggle-password" onclick="togglePassword()">👁</span>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label text-light" for="remember">
                Remember me
            </label>
        </div>

        <button type="submit" class="btn btn-login w-100">
            Login
        </button>
    </form>

    {{-- ❌ Invalid credential message (FORM level) --}}
    @if ($errors->has('login'))
        <div class="mt-3 text-center login-error">
            {{ $errors->first('login') }}
        </div>
    @endif

    {{-- 🔗 Forgot password (show-off only) --}}
    <div class="text-center mt-4">
        <a href="javascript:void(0);"
           style="color:#cbd5f5; font-size:14px; text-decoration:none;"
           onclick="forgotPasswordAlert()">
            Forgot password?
        </a>
    </div>
</div>

{{-- SweetAlert --}}
@include('backend.partials.sweetalert')

<script>
    function togglePassword() {
        const input = document.getElementById('password');
        input.type = input.type === 'password' ? 'text' : 'password';
    }

    function forgotPasswordAlert() {
        Swal.fire({
            icon: 'info',
            title: 'Contact Administrator',
            text: 'Please contact system administrator to reset your password.',
            confirmButtonColor: '#38bdf8'
        });
    }
</script>

</body>
</html>
