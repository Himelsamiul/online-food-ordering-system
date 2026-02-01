<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;


class RegistrationController extends Controller
{
    public function create()
    {
        return view('frontend.pages.registration.form');
    }

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

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('registrations', 'public');
        }

        Registration::create([
            'full_name' => $request->full_name,
            'username'  => $request->username,
            'phone'     => $request->phone,
            'email'     => $request->email,
            'dob'       => $request->dob,
            'address'   => $request->address,
            'password'  => Hash::make($request->password),
            'image'     => $imagePath,
        ]);

       return redirect()->route('login')
    ->with('success', 'Registration successful');

    }

    public function registrations()
    {
        $registrations = Registration::latest()->get();
        return view('backend.pages.registrations.index', compact('registrations'));
    }

    // ✅ Delete a registered user
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

    return view('frontend.pages.profile', compact('user'));
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



    
}
