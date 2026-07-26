<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $query = Promotion::with('coupon');

        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . trim($request->title) . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $promotions = $query->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        // Only coupons worth advertising
        $coupons = Coupon::where('status', true)->orderBy('code')->get();

        return view('backend.pages.promotions.index', compact('promotions', 'coupons'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('promotions', 'public');
        }

        Promotion::create($data);

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promo banner created successfully');
    }

    public function edit(Promotion $promotion)
    {
        $coupons = Coupon::where('status', true)->orderBy('code')->get();

        return view('backend.pages.promotions.edit', compact('promotion', 'coupons'));
    }

    public function update(Request $request, Promotion $promotion)
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            if ($promotion->image && Storage::disk('public')->exists($promotion->image)) {
                Storage::disk('public')->delete($promotion->image);
            }

            $data['image'] = $request->file('image')->store('promotions', 'public');
        }

        $promotion->update($data);

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promo banner updated successfully');
    }

    public function destroy(Promotion $promotion)
    {
        if ($promotion->image && Storage::disk('public')->exists($promotion->image)) {
            Storage::disk('public')->delete($promotion->image);
        }

        $promotion->delete();

        return back()->with('success', 'Promo banner deleted successfully');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title'      => 'required|string|max:150',
            'subtitle'   => 'nullable|string|max:255',
            'image'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'coupon_id'  => 'nullable|exists:coupons,id',
            'link_url'   => 'nullable|url|max:255',
            'starts_at'  => 'nullable|date',
            'ends_at'    => 'nullable|date|after_or_equal:starts_at',
            'sort_order' => 'nullable|integer|min:0',
            'status'     => 'required|boolean',
        ], [
            'ends_at.after_or_equal' => 'The end date must be on or after the start date.',
        ]);
    }
}
