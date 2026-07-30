<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\DeliveryMan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DeliveryManController extends Controller
{
    /**
     * Display a listing of delivery men
     */
 public function index(Request $request)
    {
        $query = DeliveryMan::query();

        // 🔍 Search by name
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // 📞 Search by phone
        if ($request->filled('phone')) {
            $query->where('phone', 'like', '%' . $request->phone . '%');
        }

        // ⚡ Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 📅 Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

    $deliveryMen = $query
        ->with('deliveryRuns.orders')
        ->latest()
        ->paginate(10)
        ->withQueryString(); // pagination e filter retain

        return view('backend.pages.delivery_man.index', compact('deliveryMen'));
    }


    /**
     * Store a newly created delivery man
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:delivery_men,email',
            'phone'      => [
                'required',
                'regex:/^(013|014|015|016|017|018|019)[0-9]{8}$/',
                'unique:delivery_men,phone'
            ],
            'address'    => 'required|string|max:500',
            'nid_number' => [
                'required',
                'regex:/^(\d{9}|\d{13})$/',
                'unique:delivery_men,nid_number'
            ],
            'photo'      => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'note'       => 'nullable|string|max:500',

            // Portal credentials. Optional — a rider who is only tracked on
            // paper does not need a login, and leaving these blank simply
            // means they cannot sign in.
            'username'   => 'nullable|string|max:60|alpha_dash|unique:delivery_men,username',
            'password'   => 'nullable|string|min:6|max:100|confirmed',
        ]);

        // image upload
        $photoPath = $request->file('photo')->store('delivery_men', 'public');

        DeliveryMan::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'address'    => $request->address,
            'nid_number' => $request->nid_number,
            'photo'      => $photoPath,
            'note'       => $request->note,
            'username'   => $request->username ?: null,
            // The model casts password => 'hashed', so this is stored hashed.
            'password'   => $request->password ?: null,
            'status'     => 1,
        ]);

        return redirect()
            ->route('admin.delivery-men.index')
            ->with('success', 'Delivery man created successfully');
    }

    /**
     * Show the form for editing the specified delivery man
     */
    public function edit(DeliveryMan $deliveryMan)
    {
        return view('backend.pages.delivery_man.edit', compact('deliveryMan'));
    }

    /**
     * Update the specified delivery man
     */
    public function update(Request $request, DeliveryMan $deliveryMan)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:delivery_men,email,' . $deliveryMan->id,
            'phone'      => [
                'required',
                'regex:/^(013|014|015|016|017|018|019)[0-9]{8}$/',
                'unique:delivery_men,phone,' . $deliveryMan->id
            ],
            'address'    => 'required|string|max:500',
            'nid_number' => [
                'required',
                'regex:/^(\d{9}|\d{13})$/',
                'unique:delivery_men,nid_number,' . $deliveryMan->id
            ],
            'photo'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'note'       => 'nullable|string|max:500',

            'username'   => 'nullable|string|max:60|alpha_dash|unique:delivery_men,username,' . $deliveryMan->id,
            // Blank means "leave the current password alone", so a rider is
            // not locked out every time their phone number is corrected.
            'password'   => 'nullable|string|min:6|max:100|confirmed',
        ]);

        // photo update (if new uploaded)
        if ($request->hasFile('photo')) {
            if ($deliveryMan->photo && Storage::disk('public')->exists($deliveryMan->photo)) {
                Storage::disk('public')->delete($deliveryMan->photo);
            }
            $deliveryMan->photo = $request->file('photo')->store('delivery_men', 'public');
        }

        $payload = [
            'name'       => $request->name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'address'    => $request->address,
            'nid_number' => $request->nid_number,
            'note'       => $request->note,
            'username'   => $request->username ?: null,
        ];

        // Only touch the password when a new one was actually typed.
        if ($request->filled('password')) {
            $payload['password'] = $request->password;
        }

        $deliveryMan->update($payload);

        return redirect()
            ->route('admin.delivery-men.index')
            ->with('success', $request->filled('password')
                ? 'Delivery man updated and their portal password reset.'
                : 'Delivery man updated successfully');
    }

    /**
     * Remove the specified delivery man
     */
public function destroy(DeliveryMan $deliveryMan)
{
    // 🔒 Check if delivery man is used anywhere
    $isUsed = $deliveryMan
        ->deliveryRuns()
        ->whereHas('orders')
        ->exists();

    if ($isUsed) {
        return redirect()
            ->back()
            ->with('error', 'This delivery man is already used in orders and cannot be deleted.');
    }

    // 🧹 Delete photo if exists
    if ($deliveryMan->photo && Storage::disk('public')->exists($deliveryMan->photo)) {
        Storage::disk('public')->delete($deliveryMan->photo);
    }

    $deliveryMan->delete();

    return redirect()
        ->route('admin.delivery-men.index')
        ->with('success', 'Delivery man deleted successfully');
}



    public function toggleStatus(DeliveryMan $deliveryMan)
{
    $deliveryMan->update([
        'status' => $deliveryMan->status ? 0 : 1
    ]);

    return redirect()
        ->back()
        ->with('success', 'Delivery man status updated successfully');
}

}
