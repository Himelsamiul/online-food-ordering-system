<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubcategoryController extends Controller
{

    public function index(Request $request)
    {
        // Active categories for dropdown
        $categories = Category::where('status', 1)->get();

        $query = Subcategory::with(['category'])
            ->withCount('foods');

        // Filter: category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter: name
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // Filter: status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $subcategories = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('backend.pages.subcategory.index', compact(
            'categories',
            'subcategories'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'      => 'required|boolean',
        ]);

        // Duplicate check
        $exists = Subcategory::where('category_id', $request->category_id)
            ->where('name', $request->name)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->with('error', 'This subcategory already exists in the selected category.');
        }

        // Image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('subcategories', 'public');
        }

        Subcategory::create([
            'category_id' => $request->category_id,
            'name'        => $request->name,
            'image'       => $imagePath,
            'status'      => $request->status,
        ]);

        return back()->with('success', 'Subcategory created successfully');
    }


    public function edit(Subcategory $subcategory)
    {
        $categories = Category::where('status', 1)->get();

        return view('backend.pages.subcategory.edit', compact(
            'subcategory',
            'categories'
        ));
    }


    public function update(Request $request, Subcategory $subcategory)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'      => 'required|boolean',
        ]);

        // Duplicate check (ignore current)
        $exists = Subcategory::where('category_id', $request->category_id)
            ->where('name', $request->name)
            ->where('id', '!=', $subcategory->id)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->with('error', 'This subcategory already exists in the selected category.');
        }

        // Image update
        $imagePath = $subcategory->image;

        if ($request->hasFile('image')) {
            // Delete old image
            if ($subcategory->image && Storage::disk('public')->exists($subcategory->image)) {
                Storage::disk('public')->delete($subcategory->image);
            }

            $imagePath = $request->file('image')->store('subcategories', 'public');
        }

        $subcategory->update([
            'category_id' => $request->category_id,
            'name'        => $request->name,
            'image'       => $imagePath,
            'status'      => $request->status,
        ]);

        return redirect()
            ->route('admin.subcategory.index')
            ->with('success', 'Subcategory updated successfully');
    }


    public function destroy(Subcategory $subcategory)
    {
        // Prevent delete if used in foods
        if ($subcategory->foods()->count() > 0) {
            return back()->with('error', 'This subcategory is already used in food items.');
        }

        // Delete image
        if ($subcategory->image && Storage::disk('public')->exists($subcategory->image)) {
            Storage::disk('public')->delete($subcategory->image);
        }

        $subcategory->delete();

        return back()->with('success', 'Subcategory deleted successfully');
    }
}
