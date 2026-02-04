<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeliveryRun;
use App\Models\DeliveryMan;
use App\Models\Order;
use Carbon\Carbon;

class DeliveryRunController extends Controller
{
    /**
     * List all delivery runs
     */
    public function index()
    {
        $runs = DeliveryRun::with('deliveryMan')
            ->latest()
            ->paginate(10);

        return view('backend.pages.delivery-runs.index', compact('runs'));
    }

    /**
     * Show create delivery run form
     */
    public function create()
    {
        $deliveryMen = DeliveryMan::where('status', 1)->get();

$orders = Order::whereIn('order_status', ['pending', 'cooking'])
    ->whereNull('delivery_run_id')
    ->get();

        return view('backend.pages.delivery-runs.create', compact('deliveryMen', 'orders'));
    }

    /**
     * 🔥 AJAX: load MULTIPLE order details (max 5)
     * delivery man can take multiple customers order
     */
public function orderDetails(Request $request)
{
    $request->validate([
        'order_ids'   => 'required|array|min:1|max:5',
        'order_ids.*' => 'exists:orders,id',
    ]);

    $orders = Order::with(['items.food'])
        ->whereIn('id', $request->order_ids)
        ->get();

    $response = [];

    foreach ($orders as $order) {

        $items = [];
        $subtotal = 0;

        foreach ($order->items as $item) {
            $line = $item->price * $item->quantity;

            $items[] = [
                'food_name' => $item->food->name ?? 'N/A',
                'price'     => $item->price,
                'quantity'  => $item->quantity,
                'subtotal'  => $line,
            ];

            $subtotal += $line;
        }

        $response[] = [
            'order_id'      => $order->id,
            'order_number'  => $order->order_number,
            'customer_name' => $order->name,
            'phone'         => $order->phone,
            'address'       => $order->address,
            'items'         => $items,
            'order_total'   => $subtotal,
        ];
    }

    return response()->json($response);
}


    /**
     * Store delivery run
     * delivery man can carry multiple customer orders (max 5)
     */
    public function store(Request $request)
    {
        $request->validate([
            'delivery_man_id' => 'required|exists:delivery_men,id',
            'order_ids'       => 'required|array|min:1|max:5',
            'order_ids.*'     => 'exists:orders,id',
            'note'            => 'nullable|string',
        ]);

        if (count($request->order_ids) > 5) {
            return back()->with('error', 'Delivery man can carry maximum 5 orders.');
        }

        $run = DeliveryRun::create([
            'delivery_man_id' => $request->delivery_man_id,
            'order_ids'       => $request->order_ids,
            'departed_at'     => Carbon::now(),
            'status'          => 'on_the_way',
            'note'            => $request->note,
        ]);

        Order::whereIn('id', $request->order_ids)->update([
            'delivery_run_id' => $run->id,
            'order_status'    => 'out_for_delivery',
        ]);

        return redirect()
            ->route('admin.delivery-runs.index')
            ->with('success', 'Delivery run started successfully.');
    }

    /**
     * Edit delivery run (delivery man & note only)
     */
    public function edit($id)
    {
        $run = DeliveryRun::findOrFail($id);
        $deliveryMen = DeliveryMan::where('status', 1)->get();

        return view('backend.pages.delivery-runs.edit', compact('run', 'deliveryMen'));
    }

    /**
     * Update delivery run
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'delivery_man_id' => 'required|exists:delivery_men,id',
            'note'            => 'nullable|string',
        ]);

        $run = DeliveryRun::findOrFail($id);

        $run->update([
            'delivery_man_id' => $request->delivery_man_id,
            'note'            => $request->note,
        ]);

        return redirect()
            ->route('delivery-runs.index')
            ->with('success', 'Delivery run updated successfully.');
    }

    /**
     * Complete delivery
     */
    public function complete($id)
    {
        $run = DeliveryRun::findOrFail($id);

        $run->update([
            'status'      => 'completed',
            'returned_at' => Carbon::now(),
        ]);

        Order::whereIn('id', $run->order_ids)->update([
            'order_status' => 'delivered',
        ]);

        return back()->with('success', 'Delivery completed successfully.');
    }

    /**
     * Delete delivery run
     */
  public function destroy($id)
{
    $run = DeliveryRun::findOrFail($id);

    // 🔥 ALWAYS rollback orders when a delivery run is deleted
    Order::whereIn('id', $run->order_ids)->update([
        'delivery_run_id' => null,
        'order_status'    => 'pending',
    ]);

    $run->delete();

    return back()->with('success', 'Delivery run deleted successfully.');
}

}
