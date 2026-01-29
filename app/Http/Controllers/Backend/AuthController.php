<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('backend.auth.login');
    }

   public function login(Request $request)
{
    $credentials = $request->only('email', 'password');
    $remember = $request->has('remember');

    if (auth()->attempt($credentials, $remember)) {

        if (!auth()->user()->is_admin) {
            auth()->logout();
            return back()->with('error', 'You are not admin');
        }

        return redirect()->route('admin.dashboard')
            ->with('success', 'Welcome back!');
    }

    return back()->with('error', 'Invalid email or password');
}

    public function logout()
{
    auth()->logout();

    return redirect()->route('admin.login')
        ->with('success', 'Logged out successfully');
}

}
