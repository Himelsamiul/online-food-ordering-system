<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
            'dob'       => 'required|date',
            'password'  => 'required|min:6|confirmed',
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
    $sessionUser = session('frontend_user');

    if (!$sessionUser) {
        return redirect()->route('login');
    }

    $user = Registration::findOrFail($sessionUser['id']);

    return view('frontend.pages.profile', compact('user'));
}




    
}
