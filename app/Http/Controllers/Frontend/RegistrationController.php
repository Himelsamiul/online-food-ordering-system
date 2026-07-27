<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\AccountStatusMail;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Order;
use App\Models\Coupon;
use App\Services\OtpService;
use App\Support\AuditLogger;


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

        if (!$this->sendRegistrationOtp($request->email)) {
            return redirect()->route('register.verify')
                ->with('info', 'A code was sent recently — check your inbox before asking for another.');
        }

        return redirect()->route('register.verify')
            ->with('success', 'We sent a verification code to your email.');
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
            'email'          => $pending['email'],
            'expiresMinutes' => app(OtpService::class)->expiryMinutes(),
        ]);
    }

    /**
     * Step 2 (POST): check the code and finally create the account.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:' . config('security.otp.length', 6),
        ]);

        $pending = session('pending_registration');

        if (!$pending) {
            return redirect()->route('register')
                ->with('error', 'Your session expired. Please register again.');
        }

        $otp    = app(OtpService::class);
        $result = $otp->verifyAndConsume(
            OtpService::GUARD_CUSTOMER,
            OtpService::PURPOSE_REGISTER,
            $pending['email'],
            $request->code,
        );

        if ($result['status'] !== OtpService::OK) {
            return back()->with('error', match ($result['status']) {
                OtpService::EXPIRED   => 'That code has expired. Ask for a new one.',
                OtpService::TOO_MANY  => 'Too many wrong attempts — that code has been cancelled. Request a new one.',
                OtpService::NOT_FOUND => 'No active code for this address. Request a new one.',
                default               => 'That code is not right. ' . $result['remaining'] . ' attempt(s) left.',
            });
        }

        $user = DB::transaction(function () use ($pending) {
            return Registration::create([
                'full_name'         => $pending['full_name'],
                'username'          => $pending['username'],
                'phone'             => $pending['phone'],
                'email'             => $pending['email'],
                'dob'               => $pending['dob'],
                'address'           => $pending['address'],
                'password'          => $pending['password'], // already hashed
                'image'             => $pending['image'],
                'is_active'         => true,
                'email_verified_at' => now(),
            ]);
        });

        AuditLogger::system(
            AuditLogger::ACTION_CUSTOMER_CREATED,
            'Customers',
            'New customer registered: ' . $user->full_name . ' (' . $user->email . ')',
            $user,
        );

        session()->forget('pending_registration');

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

        if (!$this->sendRegistrationOtp($pending['email'])) {
            $wait = app(OtpService::class)->secondsUntilResend(
                OtpService::GUARD_CUSTOMER,
                OtpService::PURPOSE_REGISTER,
                $pending['email'],
            );

            return back()->with('error', $wait > 0
                ? "Please wait {$wait} seconds before asking for another code."
                : 'Too many codes requested for this address. Try again later.');
        }

        return back()->with('success', 'A new verification code has been sent.');
    }

    /**
     * Issue and deliver a registration code.
     *
     * Backed by otp_codes rather than the session, so the code survives a
     * session regeneration and gets the same single-use, expiry and
     * attempt-limit guarantees as every other OTP in the app.
     *
     * @return bool false when the caller is being throttled
     */
    private function sendRegistrationOtp(string $email): bool
    {
        return app(OtpService::class)->issue(
            OtpService::GUARD_CUSTOMER,
            OtpService::PURPOSE_REGISTER,
            $email,
        ) !== null;
    }

    public function registrations(Request $request)
    {
        $query = Registration::query();

        if ($term = $request->query('q')) {
            $query->where(function ($q) use ($term) {
                $q->where('full_name', 'like', "%{$term}%")
                    ->orWhere('username', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%");
            });
        }

        if ($request->query('status') === 'active') {
            $query->where('is_active', true);
        } elseif ($request->query('status') === 'inactive') {
            $query->where('is_active', false);
        }

        $registrations = $query->withCount('orders')
            ->latest()
            ->paginate((int) $request->query('per_page', 20))
            ->withQueryString();

        $stats = [
            'total'    => Registration::count(),
            'active'   => Registration::where('is_active', true)->count(),
            'inactive' => Registration::where('is_active', false)->count(),
            'new'      => Registration::where('created_at', '>=', now()->subDays(30))->count(),
        ];

        return view('backend.pages.registrations.index', compact('registrations', 'stats'));
    }

    /**
     * Switch a customer account on or off.
     *
     * Deactivating tells them why on their next sign-in attempt and gives
     * them the reactivation-request form; both directions send an email.
     */
    public function toggleStatus(Request $request, $id)
    {
        $user = Registration::findOrFail($id);

        $data = $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $activating = !$user->isActive();

        DB::transaction(function () use ($user, $data, $activating) {
            $activating
                ? $user->activate()
                : $user->deactivate($data['reason'] ?? null);

            AuditLogger::system(
                $activating ? AuditLogger::ACTION_USER_ACTIVATED : AuditLogger::ACTION_USER_DEACTIVATED,
                'Customers',
                ($activating ? 'Activated' : 'Deactivated') . ' customer ' . $user->email
                    . (!$activating && !empty($data['reason']) ? ' — ' . $data['reason'] : ''),
                $user,
                ['is_active' => !$activating],
                ['is_active' => $activating],
            );
        });

        $flash = $activating
            ? $user->full_name . ' has been reactivated.'
            : $user->full_name . ' has been deactivated and can no longer sign in.';

        try {
            Mail::to($user->email)->queue(new AccountStatusMail(
                name:  $user->full_name,
                email: $user->email,
                state: $activating ? AccountStatusMail::ACTIVATED : AccountStatusMail::DEACTIVATED,
                note:  $data['reason'] ?? null,
            ));
        } catch (\Throwable $e) {
            Log::warning('Account status mail failed: ' . $e->getMessage());
            $flash .= ' (the notification email could not be sent)';
        }

        return back()->with('success', $flash);
    }

    //  Delete a registered user
    public function deleteRegistration($id)
    {
        $user = Registration::findOrFail($id);

        AuditLogger::system(
            AuditLogger::ACTION_CUSTOMER_REMOVED,
            'Customers',
            'Deleted customer ' . $user->full_name . ' (' . $user->email . ')',
            $user,
        );

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
