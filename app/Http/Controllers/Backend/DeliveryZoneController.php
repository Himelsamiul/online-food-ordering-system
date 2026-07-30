<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Delivery areas and their charges.
 *
 * Route middleware gates each verb on delivery_zones.<action>, so nothing here
 * re-checks permissions.
 */
class DeliveryZoneController extends Controller
{
    public function index(Request $request): View
    {
        $zones = DeliveryZone::query()
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%' . $request->query('q') . '%'))
            ->when($request->query('status') === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->query('status') === 'inactive', fn ($q) => $q->where('is_active', false))
            ->ordered()
            ->paginate($this->perPage($request, 20))
            ->withQueryString();

        return view('backend.pages.delivery-zones.index', [
            'zones'     => $zones,
            'canCreate' => $request->user()->hasPermission('delivery_zones.create'),
            'canEdit'   => $request->user()->hasPermission('delivery_zones.edit'),
            'canDelete' => $request->user()->hasPermission('delivery_zones.delete'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        DeliveryZone::create($this->validated($request));

        return redirect()->route('admin.delivery-zones.index')
            ->with('success', 'Delivery area added.');
    }

    public function update(Request $request, DeliveryZone $deliveryZone): RedirectResponse
    {
        $deliveryZone->update($this->validated($request));

        return redirect()->route('admin.delivery-zones.index')
            ->with('success', 'Delivery area updated.');
    }

    public function toggleStatus(DeliveryZone $deliveryZone): RedirectResponse
    {
        $deliveryZone->update(['is_active' => !$deliveryZone->is_active]);

        return back()->with(
            'success',
            $deliveryZone->name . ' is now ' . ($deliveryZone->is_active ? 'active' : 'paused') . '.'
        );
    }

    public function destroy(DeliveryZone $deliveryZone): RedirectResponse
    {
        /*
         * Orders keep delivery_zone_id, but the row is only a pointer — the
         * zone NAME is snapshotted on the order itself, so deleting a zone
         * cannot corrupt an old invoice. Still worth telling the admin how
         * many orders reference it before it disappears from their reports.
         */
        $used = Order::where('delivery_zone_id', $deliveryZone->id)->count();
        $name = $deliveryZone->name;

        $deliveryZone->delete();

        return redirect()->route('admin.delivery-zones.index')->with(
            'success',
            $used > 0
                ? "{$name} deleted. {$used} past order(s) keep the area name on their records."
                : "{$name} deleted."
        );
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:120'],
            'areas'       => ['nullable', 'string', 'max:500'],
            'charge'      => ['required', 'numeric', 'min:0', 'max:99999'],
            'min_order'   => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'free_above'  => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'eta_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'sort_order'  => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active'   => ['nullable', 'boolean'],
        ], [
            'charge.required' => 'Enter the delivery charge (use 0 for free).',
        ]);

        $data['is_active']  = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
