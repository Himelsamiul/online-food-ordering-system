<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;
use App\Support\CartTotals;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Picking a delivery area.
 *
 * The choice is parked in the session exactly like the coupon code, so the
 * cart page, the checkout page and the order that follows all read the same
 * value through CartTotals. Nothing here trusts a price sent by the browser —
 * only the zone id is accepted, and the charge is looked up server-side.
 */
class DeliveryZoneController extends Controller
{
    public function select(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'zone_id' => ['nullable', 'integer'],
        ]);

        $zoneId = $validated['zone_id'] ?? null;

        if (!$zoneId) {
            session()->forget(CartTotals::ZONE_KEY);

            return $this->payload();
        }

        $zone = DeliveryZone::active()->find($zoneId);

        if (!$zone) {
            return response()->json([
                'message' => 'We are not delivering to that area right now.',
            ], 422);
        }

        session()->put(CartTotals::ZONE_KEY, $zone->id);

        return $this->payload();
    }

    /** The recomputed totals, so the page can repaint without a reload. */
    private function payload(): JsonResponse
    {
        $totals = CartTotals::fromSession(Auth::guard('frontend')->id());

        return response()->json([
            'subtotal'         => number_format($totals->subtotal, 2),
            'discount'         => number_format($totals->discount, 2),
            'delivery_charge'  => number_format($totals->deliveryCharge, 2),
            'total'            => number_format($totals->total(), 2),
            'has_zone'         => $totals->hasZone(),
            'free_delivery'    => $totals->hasFreeDelivery(),
            'below_minimum'    => $totals->belowZoneMinimum(),
            'minimum'          => $totals->zone?->min_order
                ? number_format((float) $totals->zone->min_order, 2)
                : null,
            'to_free_delivery' => $totals->amountToFreeDelivery()
                ? number_format($totals->amountToFreeDelivery(), 2)
                : null,
            'eta'              => $totals->zone?->eta_minutes,
            'zone_name'        => $totals->zone?->name,
        ]);
    }
}
