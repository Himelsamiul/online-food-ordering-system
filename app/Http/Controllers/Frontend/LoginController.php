<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public function showLogin()
    {
        return view('frontend.pages.login');
    }



public function login(Request $request)
{
    $request->validate([
        'login'    => 'required|string',
        'password' => 'required|min:6',
    ]);

    $user = Registration::where('email', $request->login)
                ->orWhere('username', $request->login)
                ->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return back()->with('error', 'Invalid username/email or password');
    }

    Session::put('frontend_user', [
        'id'       => $user->id,
        'name'     => $user->full_name,
        'username' => $user->username,
        'email'    => $user->email,
    ]);

    
    if ($request->has('remember')) {
        session()->put('remember_me', true);
    }

    return redirect()->route('home')
        ->with('success', 'Login successful');
}



public function logout()
{
    Session::forget('frontend_user');
    Session::flush();

    return redirect()->route('home')
        ->with('success', 'Logged out successfully');
}

}
