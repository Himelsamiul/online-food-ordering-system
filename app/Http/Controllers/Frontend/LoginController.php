<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Torann\GeoIP\Facades\GeoIP;

class LoginController extends Controller
{
    // show login page
    public function showLogin()
    {
        if (Auth::guard('frontend')->check()) {
            return redirect()->route('profile');
        }

        return view('frontend.pages.login');
    }

    // handle login
    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|min:6',
        ]);

        // username OR email login
        $credentials = filter_var($request->login, FILTER_VALIDATE_EMAIL)
            ? ['email' => $request->login, 'password' => $request->password]
            : ['username' => $request->login, 'password' => $request->password];

        // auth attempt (remember me handled by Laravel)
        if (!Auth::guard('frontend')->attempt($credentials, $request->filled('remember'))) {
            return back()->with('error', 'Invalid username/email or password');
        }

        $user = Auth::guard('frontend')->user();

        // IP (local safe)
        $ip = app()->environment('local')
            ? '8.8.8.8'
            : $request->ip();

        $location = GeoIP::getLocation($ip);

        // login history
        LoginHistory::create([
            'registration_id' => $user->id,
            'ip_address'      => $ip,
            'country'         => $location->country,
            'city'            => $location->city,
            'user_agent'      => $request->userAgent(),
            'logged_in_at'    => now(),
        ]);

        return redirect()->route('profile')
            ->with('success', 'Login successful');
    }

    // logout
    public function logout()
    {
        $user = Auth::guard('frontend')->user();

        if ($user) {
            LoginHistory::where('registration_id', $user->id)
                ->whereNull('logged_out_at')
                ->latest()
                ->first()
                ?->update([
                    'logged_out_at' => now(),
                ]);
        }

        Auth::guard('frontend')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Logged out successfully');
    }




    // login history for admin;

public function loginHistory(Request $request)
{
    // pagination limit (default 20)
    $perPage = $request->get('per_page', 20);

    $histories = LoginHistory::with('registration')
        ->latest('logged_in_at')
        ->paginate($perPage)
        ->withQueryString(); // dropdown change করলে page reset না হয়

    return view('backend.pages.loginhistory', compact('histories', 'perPage'));
}

}
