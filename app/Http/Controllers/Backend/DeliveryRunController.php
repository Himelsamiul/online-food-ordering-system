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
public function index(Request $request)
{
    $query = DeliveryRun::with('deliveryMan');

    // 🔍 Filter: Delivery Man (dropdown)
    if ($request->filled('delivery_man_id')) {
        $query->where('delivery_man_id', $request->delivery_man_id);
    }

    // 📞 Filter: Customer phone
    if ($request->filled('phone')) {
        $query->whereHas('orders', function ($q) use ($request) {
            $q->where('phone', 'like', '%' . $request->phone . '%');
        });
    }

    // ⚡ Filter: Status
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // 📅 Filter: From date
    if ($request->filled('from_date')) {
        $query->whereDate('departed_at', '>=', $request->from_date);
    }

    // 📅 Filter: To date
    if ($request->filled('to_date')) {
        $query->whereDate('departed_at', '<=', $request->to_date);
    }

    $runs = $query
        ->latest()
        ->paginate(10)
        ->withQueryString(); // 🔥 filter retain during pagination

    // 🔽 Filter dropdown data
    $deliveryMen = DeliveryMan::orderBy('name')->get();

    return view(
        'backend.pages.delivery-runs.index',
        compact('runs', 'deliveryMen')
    );
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

        /*
         * Saved one at a time rather than as a mass update.
         * Order::whereIn(...)->update(...) bypasses Eloquent events, so the
         * OrderObserver would never fire and the customer would never be told
         * their food had been picked up. A run is capped at 5 orders, so the
         * loop costs nothing.
         */
        foreach (Order::whereIn('id', $request->order_ids)->get() as $order) {
            $order->delivery_run_id = $run->id;
            $order->order_status    = 'out_for_delivery';
            $order->save();
        }

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

        // Model saves, not a mass update — see the note in store(): events must
        // fire so the customer is told their order arrived.
        foreach (Order::whereIn('id', $run->order_ids)->get() as $order) {
            $order->order_status = 'delivered';
            $order->save();
        }

        return back()->with('success', 'Delivery completed successfully.');
    }

    /**
     * Delete delivery run
     */
  public function destroy($id)
{
    $run = DeliveryRun::findOrFail($id);

    /*
     * ALWAYS rollback orders when a delivery run is deleted.
     *
     * Left as a mass update on purpose — unlike store() and complete(), this
     * transition must NOT reach the customer. Being bounced back to "pending"
     * because an admin removed a run is internal bookkeeping; telling someone
     * their food un-departed would only alarm them. Skipping model events is
     * how that is achieved, so do not "fix" this into a loop.
     */
    Order::whereIn('id', $run->order_ids)->update([
        'delivery_run_id' => null,
        'order_status'    => 'pending',
    ]);

    $run->delete();

    return back()->with('success', 'Delivery run deleted successfully.');
}

    /**
     * Show delivery run details
     */ 
public function show($id)
{
    $run = DeliveryRun::with('deliveryMan')->findOrFail($id);

    $orders = Order::with(['items.food'])
        ->whereIn('id', $run->order_ids)
        ->get();

    return view(
        'backend.pages.delivery-runs.show',
        compact('run', 'orders')
    );
}

}
