<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Coupon;
use App\Support\Otp;


class RegistrationController extends Controller
{
    public function create()
    {
        return view('frontend.pages.registration.form');
    }

    /**
     * Step 1: validate the form, stash it in the session, and email a
     * verification code. The account is NOT created yet.
     */
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'username'  => 'required|string|max:100|unique:registrations,username',
            'phone'     => [
                'required',
                'regex:/^(013|014|015|016|017|018|019)[0-9]{8}$/',
                'unique:registrations,phone'
            ],
            'email'     => 'required|email|unique:registrations,email',
            'dob'       => 'required|date|before:today',
            'password'  => 'required|min:6|confirmed',
            'address'   => 'nullable|string|max:500',
            'image'     => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Store the image now; keep only the path in the session.
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('registrations', 'public');
        }

        session()->put('pending_registration', [
            'full_name' => $request->full_name,
            'username'  => $request->username,
            'phone'     => $request->phone,
            'email'     => $request->email,
            'dob'       => $request->dob,
            'address'   => $request->address,
            'password'  => Hash::make($request->password),
            'image'     => $imagePath,
        ]);

        $this->sendRegistrationOtp($request->email);

        return redirect()->route('register.verify')
            ->with('success', 'We sent a 6-digit verification code to your email.');
    }

    /**
     * Step 2 (GET): show the code-entry page.
     */
    public function showVerify()
    {
        $pending = session('pending_registration');

        if (!$pending) {
            return redirect()->route('register')
                ->with('error', 'Please fill the registration form first.');
        }

        return view('frontend.pages.registration.verify', [
            'email' => $pending['email'],
        ]);
    }

    /**
     * Step 2 (POST): check the code and finally create the account.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);

        $pending = session('pending_registration');
        $otp     = session('pending_registration_otp');

        if (!$pending || !$otp) {
            return redirect()->route('register')
                ->with('error', 'Your session expired. Please register again.');
        }

        if (now()->timestamp > $otp['expires']) {
            return back()->with('error', 'The code has expired. Please resend a new code.');
        }

        if (!Hash::check($request->code, $otp['hash'])) {
            return back()->with('error', 'Invalid verification code.');
        }

        $user = Registration::create([
            'full_name'         => $pending['full_name'],
            'username'          => $pending['username'],
            'phone'             => $pending['phone'],
            'email'             => $pending['email'],
            'dob'               => $pending['dob'],
            'address'           => $pending['address'],
            'password'          => $pending['password'], // already hashed
            'image'             => $pending['image'],
            'email_verified_at' => now(),
        ]);

        session()->forget(['pending_registration', 'pending_registration_otp']);

        Auth::guard('frontend')->login($user);

        return redirect()->route('profile')
            ->with('success', 'Email verified — welcome!');
    }

    /**
     * Resend a fresh code for the pending registration.
     */
    public function resendOtp()
    {
        $pending = session('pending_registration');

        if (!$pending) {
            return redirect()->route('register')
                ->with('error', 'Please fill the registration form first.');
        }

        $this->sendRegistrationOtp($pending['email']);

        return back()->with('success', 'A new verification code has been sent.');
    }

    /**
     * Generate + store (hashed) + deliver a registration OTP.
     */
    private function sendRegistrationOtp(string $email): void
    {
        $code = Otp::generate();

        session()->put('pending_registration_otp', [
            'hash'    => Hash::make($code),
            'expires' => now()->addMinutes(Otp::EXPIRES_MINUTES)->timestamp,
        ]);

        Otp::deliver($email, $code, 'register');
    }

    public function registrations()
    {
        $registrations = Registration::latest()->get();
        return view('backend.pages.registrations.index', compact('registrations'));
    }

    //  Delete a registered user
    public function deleteRegistration($id)
    {
        $user = Registration::findOrFail($id);

        // delete image if exists
        if ($user->image && file_exists(public_path('storage/' . $user->image))) {
            unlink(public_path('storage/' . $user->image));
        }

        $user->delete();

        return back()->with('success', 'User deleted successfully');
    }

    // User profile
public function profile()
{
    $user = Auth::guard('frontend')->user();

    if (!$user) {
        return redirect()->route('login');
    }

    // logged-in user er orders
    $orders = Order::where('user_id', $user->id)
        ->latest()
        ->get();

    // Offers this customer can still use — coupons they have already used
    // up to their personal limit are filtered out.
    $offers = Coupon::currentlyRunning()
        ->withCount(['usages' => fn ($q) => $q->where('registration_id', $user->id)])
        ->orderBy('expires_at')
        ->get()
        ->filter(fn ($c) => $c->per_user_limit === null || $c->usages_count < $c->per_user_limit)
        ->values();

    return view('frontend.pages.profile', compact('user', 'orders', 'offers'));
}

// Show edit profile form
public function editProfile()
{
    $user = Auth::guard('frontend')->user();

    if (!$user) {
        return redirect()->route('login');
    }

    return view('frontend.pages.profile-edit', compact('user'));
}


// Update profile
public function updateProfile(Request $request)
{
    $user = Auth::guard('frontend')->user();

    if (!$user) {
        return redirect()->route('login');
    }

    $request->validate([
        'full_name' => 'required|string|max:255',
        'username'  => 'required|string|max:100|unique:registrations,username,' . $user->id,
        'phone'     => [
            'required',
            'regex:/^(013|014|015|016|017|018|019)[0-9]{8}$/',
            'unique:registrations,phone,' . $user->id
        ],
        'email' => 'required|email|unique:registrations,email,' . $user->id,
        'dob'   => 'required|date|before:today',
        'address' => 'nullable|string|max:500',
        'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    if ($request->hasFile('image')) {
        if ($user->image && file_exists(public_path('storage/'.$user->image))) {
            unlink(public_path('storage/'.$user->image));
        }

        $user->image = $request->file('image')->store('registrations', 'public');
    }

    $user->update($request->only([
        'full_name','username','phone','email','dob','address'
    ]));

    return redirect()->route('profile')
        ->with('success', 'Profile updated successfully');
}

public function viewOrder(Order $order)
{
    $user = Auth::guard('frontend')->user();

    if ($order->user_id !== $user->id) {
        abort(403);
    }

    $order->load('items.food');

    return view('frontend.pages.order.view', compact('order'));
}


    
}
