<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\DeliveryRun;
use App\Models\Order;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * What a rider sees on their phone.
 *
 * Every lookup is scoped to the signed-in rider's own runs. `assertOwned()` is
 * the single choke point for that, so a rider cannot mark somebody else's
 * order delivered by editing an id in the URL.
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        $rider = Auth::guard('rider')->user();

        $runs = DeliveryRun::where('delivery_man_id', $rider->id)
            ->where('status', 'on_the_way')
            ->latest('departed_at')
            ->get();

        // order_ids is a JSON array on the run, so the orders are fetched in
        // one query and grouped back rather than N+1'd per run.
        $orderIds = $runs->flatMap(fn (DeliveryRun $run) => $run->order_ids ?? [])->unique()->all();

        $orders = Order::whereIn('id', $orderIds)
            ->with('items.food:id,name')
            ->get()
            ->keyBy('id');

        $todayDelivered = Order::whereIn('id', function ($query) use ($rider) {
                $query->select('id')->from('orders')
                    ->whereIn('delivery_run_id', DeliveryRun::where('delivery_man_id', $rider->id)->select('id'));
            })
            ->where('order_status', 'delivered')
            ->whereDate('updated_at', today())
            ->count();

        return view('rider.dashboard', [
            'rider'          => $rider,
            'runs'           => $runs,
            'orders'         => $orders,
            'todayDelivered' => $todayDelivered,
        ]);
    }

    /** History: runs this rider has already closed out. */
    public function history(Request $request): View
    {
        $rider = Auth::guard('rider')->user();

        $runs = DeliveryRun::where('delivery_man_id', $rider->id)
            ->where('status', 'completed')
            ->latest('returned_at')
            ->paginate($this->perPage($request, 15));

        return view('rider.history', ['runs' => $runs, 'rider' => $rider]);
    }

    /**
     * Mark one order delivered.
     *
     * Per order rather than per run: a rider carrying five drops finishes them
     * one at a time, and each customer should hear about theirs when it
     * actually lands, not when the whole run closes.
     */
    public function markDelivered(Order $order): RedirectResponse
    {
        $rider = Auth::guard('rider')->user();
        $run   = $this->assertOwned($order, $rider->id);

        if ($order->order_status === 'delivered') {
            return back()->with('info', 'That order was already marked delivered.');
        }

        DB::transaction(function () use ($order, $run, $rider) {
            // A model save, not a mass update — the OrderObserver has to fire
            // so the customer's notification bell rings.
            $order->order_status = 'delivered';
            $order->save();

            AuditLogger::system(
                AuditLogger::ACTION_STATUS_CHANGED,
                'Delivery Runs',
                "Rider {$rider->name} delivered order {$order->order_number}",
                $order,
                ['order_status' => 'out_for_delivery'],
                ['order_status' => 'delivered'],
            );

            $this->closeRunIfFinished($run, $rider);
        });

        return back()->with('success', "Order {$order->order_number} marked delivered.");
    }

    /** Close the whole run at once, for a rider heading back empty. */
    public function completeRun(DeliveryRun $deliveryRun): RedirectResponse
    {
        $rider = Auth::guard('rider')->user();

        abort_unless((int) $deliveryRun->delivery_man_id === (int) $rider->id, 403);

        DB::transaction(function () use ($deliveryRun, $rider) {
            foreach (Order::whereIn('id', $deliveryRun->order_ids ?? [])->get() as $order) {
                if ($order->order_status === 'delivered') {
                    continue;
                }

                $order->order_status = 'delivered';
                $order->save();
            }

            $deliveryRun->update(['status' => 'completed', 'returned_at' => now()]);

            AuditLogger::system(
                AuditLogger::ACTION_STATUS_CHANGED,
                'Delivery Runs',
                "Rider {$rider->name} completed run #{$deliveryRun->id}",
                $deliveryRun,
                ['status' => 'on_the_way'],
                ['status' => 'completed'],
            );
        });

        return redirect()->route('rider.dashboard')->with('success', 'Run completed. Nice work.');
    }

    /* -------------------------------------------------------------- internal */

    /**
     * The order must belong to a run assigned to THIS rider.
     *
     * @return DeliveryRun the run it belongs to
     */
    private function assertOwned(Order $order, int $riderId): DeliveryRun
    {
        abort_if($order->delivery_run_id === null, 403);

        $run = DeliveryRun::find($order->delivery_run_id);

        abort_if(!$run || (int) $run->delivery_man_id !== $riderId, 403);

        return $run;
    }

    /** Once every order on a run has landed, the run is done. */
    private function closeRunIfFinished(DeliveryRun $run, $rider): void
    {
        $outstanding = Order::whereIn('id', $run->order_ids ?? [])
            ->whereNotIn('order_status', ['delivered', 'cancelled'])
            ->count();

        if ($outstanding > 0) {
            return;
        }

        $run->update(['status' => 'completed', 'returned_at' => now()]);

        AuditLogger::system(
            AuditLogger::ACTION_STATUS_CHANGED,
            'Delivery Runs',
            "Run #{$run->id} closed automatically — every order delivered by {$rider->name}",
            $run,
            ['status' => 'on_the_way'],
            ['status' => 'completed'],
        );
    }
}
