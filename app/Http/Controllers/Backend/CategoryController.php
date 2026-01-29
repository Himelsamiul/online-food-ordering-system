<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::latest()->paginate(10);
        return view('backend.pages.category.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|boolean'
        ]);

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

        Category::findOrFail($id)
            ->update($request->only('name','description','status'));

        return redirect()->route('admin.category.index')
            ->with('success','Category updated successfully');
    }


    public function destroy($id)
{
    Category::findOrFail($id)->delete();

    return redirect()->route('admin.category.index')
        ->with('success', 'Category deleted successfully');
}
}