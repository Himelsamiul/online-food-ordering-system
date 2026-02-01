<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $query = Unit::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
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

        $units = $query
            ->with(['foods:id,name,unit_id'])
            ->withCount('foods')
            ->latest()
            ->paginate(10);

        return view('backend.pages.units.index', compact('units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'status' => 'required|boolean',
        ]);

        $name = strtolower(trim($request->name));

        if (Unit::where('name', $name)->exists()) {
            return back()
                ->withInput()
                ->with('error', 'This unit already exists.');
        }

        Unit::create([
            'name'   => $name,
            'status' => $request->status,
        ]);

        return back()->with('success', 'Unit created successfully.');
    }

    public function edit($id)
    {
        $unit = Unit::findOrFail($id);
        return view('backend.pages.units.edit', compact('unit'));
    }

    public function update(Request $request, $id)
    {
        $unit = Unit::findOrFail($id);

        $request->validate([
            'name'   => 'required|string|max:255',
            'status' => 'required|boolean',
        ]);

        $name = strtolower(trim($request->name));

        $exists = Unit::where('name', $name)
            ->where('id', '!=', $unit->id)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->with('error', 'This unit already exists.');
        }

        $unit->update([
            'name'   => $name,
            'status' => $request->status,
        ]);

        return redirect()
            ->route('admin.units.index')
            ->with('success', 'Unit updated successfully.');
    }

    public function delete($id)
    {
        $unit = Unit::withCount('foods')->findOrFail($id);

        if ($unit->foods_count > 0) {
            return back()->with('error', 'This unit is used in food items and cannot be deleted.');
        }

        $unit->delete();

        return back()->with('success', 'Unit deleted successfully.');
    }
}
