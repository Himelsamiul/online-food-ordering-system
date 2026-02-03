<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Food;
use App\Models\Subcategory;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FoodController extends Controller
{
    public function index(Request $request)
    {
        $query = Food::with(['subcategory.category', 'unit']);

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->subcategory_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59',
            ]);
        }

        $foods = $query->latest()->paginate(10);
        $subcategories = Subcategory::where('status', 1)->with('category')->get();

        return view('backend.pages.food.index', compact('foods', 'subcategories'));
    }

    public function create()
    {
        $subcategories = Subcategory::where('status', 1)->with('category')->get();
        $units = Unit::where('status', 1)->get();

        return view('backend.pages.food.create', compact('subcategories', 'units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:foods,name',
            'subcategory_id' => 'required|exists:subcategories,id',
            'unit_id' => 'required|exists:units,id',
            'price' => 'required|numeric|min:1',
            'discount' => 'nullable|numeric|min:0|lte:price',
            'quantity' => 'required|integer|min:1',
            'low_stock_alert' => 'nullable|integer|min:0|lte:quantity',
            'barcode' => 'nullable|string|max:50',

            // IMAGE VALIDATION
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);

        $subcategory = Subcategory::findOrFail($request->subcategory_id);
        $prefix = strtoupper(substr($subcategory->name, 0, 2));
        $sku = $prefix . '-' . rand(100, 999) . '-' . rand(100, 999);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('foods', 'public');
        }

        Food::create([
            'name' => $request->name,
            'sku' => $sku,
            'subcategory_id' => $request->subcategory_id,
            'unit_id' => $request->unit_id,
            'price' => $request->price,
            'discount' => $request->discount,
            'quantity' => $request->quantity,
            'low_stock_alert' => $request->low_stock_alert,
            'barcode' => $request->barcode,
            'image' => $imagePath,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.foods.index')
            ->with('success', 'Food created successfully');
    }

    public function edit($id)
    {
        $food = Food::findOrFail($id);
        $subcategories = Subcategory::where('status', 1)->with('category')->get();
        $units = Unit::where('status', 1)->get();

        return view('backend.pages.food.edit', compact('food', 'subcategories', 'units'));
    }

    public function update(Request $request, $id)
    {
        $food = Food::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:foods,name,' . $food->id,
            'subcategory_id' => 'required|exists:subcategories,id',
            'unit_id' => 'required|exists:units,id',
            'price' => 'required|numeric|min:1',
            'discount' => 'nullable|numeric|min:0|lte:price',
            'quantity' => 'required|integer|min:1',
            'low_stock_alert' => 'nullable|integer|min:0|lte:quantity',
            'barcode' => 'nullable|string|max:50',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);

        if ($request->hasFile('image')) {
            if ($food->image && Storage::disk('public')->exists($food->image)) {
                Storage::disk('public')->delete($food->image);
            }
            $food->image = $request->file('image')->store('foods', 'public');
        }

        $food->update($request->only([
            'name',
            'subcategory_id',
            'unit_id',
            'price',
            'discount',
            'quantity',
            'low_stock_alert',
            'barcode',
            'description',
            'status',
        ]));

        return redirect()->route('admin.foods.index')
            ->with('success', 'Food updated successfully');
    }

    public function delete($id)
    {
        $food = Food::findOrFail($id);

        if ($food->image && Storage::disk('public')->exists($food->image)) {
            Storage::disk('public')->delete($food->image);
        }

        $food->delete();

        return back()->with('success', 'Food deleted successfully');
    }



    public function show($id)
{
    $food = Food::with([
        'subcategory.category',
        'unit'
    ])->findOrFail($id);

    $price = $food->price;
    $discountPercent = $food->discount ?? 0;
    $discountAmount = ($price * $discountPercent) / 100;
    $finalPrice = $price - $discountAmount;

    return view('backend.pages.food.show', compact(
        'food',
        'price',
        'discountPercent',
        'discountAmount',
        'finalPrice'
    ));
}
}
