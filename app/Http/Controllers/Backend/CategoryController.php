<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\SubCategory;

class CategoryController extends Controller
{
public function index(Request $request)
{
    $query = Category::query();

    //  Search by category name
    if ($request->filled('name')) {
        $query->where('name', 'like', '%' . $request->name . '%');
    }

    //  Filter by status
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    //  From date
    if ($request->filled('from_date')) {
        $query->whereDate('created_at', '>=', $request->from_date);
    }

    //  To date
    if ($request->filled('to_date')) {
        $query->whereDate('created_at', '<=', $request->to_date);
    }

    // Pagination + keep filter values
    $categories = $query->latest()
                        ->paginate(10)
                        ->withQueryString();

    return view('backend.pages.category.index', compact('categories'));
}
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|boolean'
        ]);
    $exists = Category::where('name', $request->name)->exists();

    if ($exists) {
        return redirect()->back()
            ->withInput()
            ->with('error', 'This category already exists.');
    }

        Category::create($request->only('name','description','status'));

        return redirect()->route('admin.category.index')
            ->with('success','Category created successfully');
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return view('backend.pages.category.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|boolean'
        ]);

    // 🔴 check duplicate except current ID
    $exists = Category::where('name', $request->name)
        ->where('id', '!=', $id)
        ->exists();

    if ($exists) {
        return redirect()->back()
            ->withInput()
            ->with('error', 'This category name already exists.');
    }


        Category::findOrFail($id)
            ->update($request->only('name','description','status'));

        return redirect()->route('admin.category.index')
            ->with('success','Category updated successfully');
    }


public function destroy($id)
{
    $hasSub = SubCategory::where('category_id', $id)->exists();

    if ($hasSub) {
        return redirect()->route('admin.category.index')
            ->with('error', 'This category has subcategories. Delete them first.');
    }

    Category::findOrFail($id)->delete();

    return redirect()->route('admin.category.index')
        ->with('success', 'Category deleted successfully');
}

}