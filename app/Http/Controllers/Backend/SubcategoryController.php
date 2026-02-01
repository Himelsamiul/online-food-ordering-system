<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    // index + create form
    public function index(Request $request)
{
    // Category list for dropdown
    $categories = Category::where('status', 1)->get();

    // Base query
    $query = Subcategory::with('category');

    // 🔍 Filter by category
    if ($request->filled('category_id')) {
        $query->where('category_id', $request->category_id);
    }

    // Search by subcategory name
    if ($request->filled('name')) {
        $query->where('name', 'like', '%' . $request->name . '%');
    }

    //  Filter by status
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // From date
    if ($request->filled('from_date')) {
        $query->whereDate('created_at', '>=', $request->from_date);
    }

    //  To date
    if ($request->filled('to_date')) {
        $query->whereDate('created_at', '<=', $request->to_date);
    }

    // Pagination + keep filters
    $subcategories = $query->latest()
        ->paginate(10)
        ->withQueryString();

    return view('backend.pages.subcategory.index', compact(
        'categories',
        'subcategories'
    ));
}

    // store
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'status'      => 'required|boolean',
        ]);

        // 🔴 Duplicate check (same category + same name)
        $exists = Subcategory::where('category_id', $request->category_id)
            ->where('name', $request->name)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->with('error', 'This subcategory already exists in the selected category.');
        }

        Subcategory::create([
            'category_id' => $request->category_id,
            'name'        => $request->name,
            'status'      => $request->status,
        ]);

        return back()->with('success', 'Subcategory created successfully');
    }

    // edit page
    public function edit(Subcategory $subcategory)
    {
        $categories = Category::where('status', 1)->get();

        return view('backend.pages.subcategory.edit', compact(
            'subcategory',
            'categories'
        ));
    }

    // update
    public function update(Request $request, Subcategory $subcategory)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'status'      => 'required|boolean',
        ]);

        // 🔴 Duplicate check (except current subcategory)
        $exists = Subcategory::where('category_id', $request->category_id)
            ->where('name', $request->name)
            ->where('id', '!=', $subcategory->id)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->with('error', 'This subcategory already exists in the selected category.');
        }

        $subcategory->update([
            'category_id' => $request->category_id,
            'name'        => $request->name,
            'status'      => $request->status,
        ]);

        return redirect()
            ->route('admin.subcategory.index')
            ->with('success', 'Subcategory updated successfully');
    }

    // delete
    public function destroy(Subcategory $subcategory)
    {
        $subcategory->delete();

        return back()->with('success', 'Subcategory deleted successfully');
    }
}
