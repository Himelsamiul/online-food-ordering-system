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
$fakeIps = [

    // 🇺🇸 United States
    '8.8.8.8',

    // 🇨🇦 Canada
    '99.79.0.1',

    // 🇬🇧 United Kingdom
    '81.2.69.142',

    // 🇩🇪 Germany
    '91.198.174.192',

    // 🇫🇷 France
    '62.210.0.1',

    // 🇳🇱 Netherlands
    '5.79.64.0',

    // 🇧🇩 Bangladesh
    '103.21.244.0',

    // 🇮🇳 India
    '49.44.0.1',

    // 🇵🇰 Pakistan
    '39.32.0.1',

    // 🇱🇰 Sri Lanka
    '112.134.0.1',

    // 🇯🇵 Japan
    '43.224.0.1',

    // 🇨🇳 China
    '36.112.0.1',

    // 🇸🇬 Singapore
    '139.99.0.1',

    // 🇦🇺 Australia
    '1.1.1.1',

    // 🇳🇿 New Zealand
    '202.89.32.1',

    // 🇧🇷 Brazil
    '191.96.0.1',

    // 🇦🇷 Argentina
    '190.16.0.1',

    // 🇨🇱 Chile
    '190.107.0.1',

    // 🇲🇽 Mexico
    '187.188.0.1',

    // 🇿🇦 South Africa
    '102.165.0.1',

    // 🇳🇬 Nigeria
    '105.112.0.1',

    // 🇪🇬 Egypt
    '41.32.0.1',

    // 🇸🇦 Saudi Arabia
    '95.177.0.1',

    // 🇹🇷 Turkey
    '88.255.0.1',

    // 🇷🇺 Russia
    '5.8.0.1',

    // 🇪🇸 Spain
    '83.44.0.1',

    // 🇮🇹 Italy
    '79.0.0.1',

    // 🇸🇪 Sweden
    '85.224.0.1',

    // 🇳🇴 Norway
    '84.48.0.1',

    // 🇫🇮 Finland
    '62.236.0.1',
];


$ip = app()->environment('local')
    ? $fakeIps[array_rand($fakeIps)]
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
    $perPage = $request->get('per_page', 20);

    $query = LoginHistory::with('registration')
        ->latest('logged_in_at');

    // 🔍 Search by name / username
    if ($request->filled('name')) {
        $query->whereHas('registration', function ($q) use ($request) {
            $q->where('full_name', 'like', '%' . $request->name . '%')
              ->orWhere('username', 'like', '%' . $request->name . '%');
        });
    }

    // 🌍 Search by country
    if ($request->filled('country')) {
        $query->where('country', 'like', '%' . $request->country . '%');
    }

    // 📅 Search by date (login date)
    if ($request->filled('date')) {
        $query->whereDate('logged_in_at', $request->date);
    }

    $histories = $query
        ->paginate($perPage)
        ->withQueryString();

    return view('backend.pages.loginhistory', compact('histories'));
}
}
