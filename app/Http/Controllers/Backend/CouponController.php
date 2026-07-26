<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $query = Coupon::withCount('usages');

        if ($request->filled('code')) {
            $query->where('code', 'like', '%' . trim($request->code) . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $coupons = $query->latest()->paginate(10)->withQueryString();

        return view('backend.pages.coupons.index', compact('coupons'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        Coupon::create($data);

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon created successfully');
    }

    public function edit(Coupon $coupon)
    {
        return view('backend.pages.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $data = $this->validated($request, $coupon->id);

        $coupon->update($data);

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon updated successfully');
    }

    public function destroy(Coupon $coupon)
    {
        if ($coupon->usages()->exists()) {
            return back()->with(
                'error',
                'This coupon has already been used on orders. Deactivate it instead of deleting.'
            );
        }

        $coupon->delete();

        return back()->with('success', 'Coupon deleted successfully');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $rules = [
            'code'             => ['required', 'string', 'max:40', 'regex:/^[A-Za-z0-9_-]+$/',
                                   Rule::unique('coupons', 'code')->ignore($ignoreId)],
            'description'      => 'nullable|string|max:255',
            'type'             => 'required|in:percent,fixed',
            'value'            => 'required|numeric|min:0.01',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount'     => 'nullable|numeric|min:0',
            'starts_at'        => 'nullable|date',
            'expires_at'       => 'nullable|date|after_or_equal:starts_at',
            'usage_limit'      => 'nullable|integer|min:1',
            'per_user_limit'   => 'nullable|integer|min:1',
            'status'           => 'required|boolean',
        ];

        // A percentage above 100 would hand out more than the cart is worth.
        if ($request->input('type') === 'percent') {
            $rules['value'] = 'required|numeric|min:0.01|max:100';
        }

        $data = $request->validate($rules, [
            'value.max'       => 'A percentage coupon cannot be more than 100%.',
            'code.regex'      => 'Coupon code may only contain letters, numbers, dashes and underscores.',
            'expires_at.after_or_equal' => 'The expiry date must be on or after the start date.',
        ]);

        // max_discount only means anything for percentage coupons
        if ($data['type'] === 'fixed') {
            $data['max_discount'] = null;
        }

        return $data;
    }
}
