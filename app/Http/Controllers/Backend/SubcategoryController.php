<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    // index + create form
    public function index()
    {
        $categories = Category::where('status', 1)->get();
        $subcategories = Subcategory::with('category')
    ->latest()
    ->paginate(10);

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
        ]);

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
        ]);

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
