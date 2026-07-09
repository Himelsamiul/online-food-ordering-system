<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Food;
use App\Models\PosSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    /**
     * POS terminal screen.
     */
    public function index(Request $request)
    {
        $foods = Food::with('subcategory.category')
            ->where('status', 1)
            ->where('quantity', '>', 0)
            ->orderBy('name')
            ->get();

        // Categories that actually have in-stock active foods (for the filter bar)
        $categories = Category::where('status', 1)
            ->orderBy('name')
            ->get();

        return view('backend.pages.pos.index', compact('foods', 'categories'));
    }

    /**
     * Store a completed sale.
     * All money is recomputed server-side from the DB — client totals are never trusted.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'order_type'          => 'required|in:dine_in,takeaway,delivery',
            'table_no'            => 'nullable|string|max:30|required_if:order_type,dine_in',
            'customer_name'       => 'nullable|string|max:255',
            'phone'               => 'nullable|string|max:20',
            'address'             => 'nullable|string|max:500|required_if:order_type,delivery',

            'items'               => 'required|array|min:1',
            'items.*.food_id'     => 'required|integer|exists:foods,id',
            'items.*.quantity'    => 'required|integer|min:1',

            'discount_type'       => 'required|in:flat,percent',
            'discount_value'      => 'nullable|numeric|min:0',
            'tax_rate'            => 'nullable|numeric|min:0|max:100',
            'service_charge_rate' => 'nullable|numeric|min:0|max:100',

            'payment_method'      => 'required|in:cash,card',
            'paid_amount'         => 'nullable|numeric|min:0',
            'note'                => 'nullable|string|max:500',
        ]);

        // Collapse duplicate food rows into a single line with summed quantity.
        $wanted = [];
        foreach ($data['items'] as $row) {
            $id = (int) $row['food_id'];
            $wanted[$id] = ($wanted[$id] ?? 0) + (int) $row['quantity'];
        }

        try {
            $sale = DB::transaction(function () use ($data, $wanted) {

                // Lock the food rows for the duration of the transaction (prevents overselling).
                $foods = Food::whereIn('id', array_keys($wanted))
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $subtotal   = 0;
                $itemsToSave = [];

                foreach ($wanted as $foodId => $qty) {
                    $food = $foods->get($foodId);

                    if (!$food || (int) $food->status !== 1) {
                        throw ValidationException::withMessages([
                            'items' => "One of the selected items is no longer available.",
                        ]);
                    }

                    if ($food->quantity < $qty) {
                        throw ValidationException::withMessages([
                            'items' => "Not enough stock for \"{$food->name}\" (only {$food->quantity} left).",
                        ]);
                    }

                    // Unit price = food price after its own discount % (same rule as the customer cart).
                    $discountPct = $food->discount ?? 0;
                    $unitPrice   = $discountPct > 0
                        ? round($food->price - (($food->price * $discountPct) / 100), 2)
                        : (float) $food->price;

                    $lineTotal = round($unitPrice * $qty, 2);
                    $subtotal += $lineTotal;

                    $itemsToSave[] = [
                        'food_id'  => $food->id,
                        'name'     => $food->name,
                        'price'    => $unitPrice,
                        'quantity' => $qty,
                        'total'    => $lineTotal,
                    ];
                }

                // ---- Bill charges (recomputed) ----
                $discountType  = $data['discount_type'];
                $discountValue = (float) ($data['discount_value'] ?? 0);

                $discountAmount = $discountType === 'percent'
                    ? round($subtotal * min($discountValue, 100) / 100, 2)
                    : min($discountValue, $subtotal);
                $discountAmount = max(0, round($discountAmount, 2));

                $taxable = max(0, round($subtotal - $discountAmount, 2));

                $taxRate = (float) ($data['tax_rate'] ?? 0);
                $taxAmount = round($taxable * $taxRate / 100, 2);

                $scRate = (float) ($data['service_charge_rate'] ?? 0);
                $scAmount = round($taxable * $scRate / 100, 2);

                $total = round($taxable + $taxAmount + $scAmount, 2);

                // ---- Payment ----
                $paymentMethod = $data['payment_method'];
                if ($paymentMethod === 'cash') {
                    $paid = (float) ($data['paid_amount'] ?? 0);
                    if ($paid < $total) {
                        throw ValidationException::withMessages([
                            'paid_amount' => 'Cash received is less than the bill total.',
                        ]);
                    }
                    $change = round($paid - $total, 2);
                } else {
                    $paid   = $total; // card is charged for the exact amount
                    $change = 0;
                }

                // ---- Persist ----
                $sale = PosSale::create([
                    'invoice_no'            => 'TMP', // replaced right after we know the id
                    'order_type'            => $data['order_type'],
                    'table_no'              => $data['order_type'] === 'dine_in' ? ($data['table_no'] ?? null) : null,
                    'customer_name'         => $data['customer_name'] ?? null,
                    'phone'                 => $data['phone'] ?? null,
                    'address'               => $data['order_type'] === 'delivery' ? ($data['address'] ?? null) : null,
                    'subtotal'              => $subtotal,
                    'discount_type'         => $discountType,
                    'discount_value'        => $discountValue,
                    'discount_amount'       => $discountAmount,
                    'tax_rate'              => $taxRate,
                    'tax_amount'            => $taxAmount,
                    'service_charge_rate'   => $scRate,
                    'service_charge_amount' => $scAmount,
                    'total'                 => $total,
                    'payment_method'        => $paymentMethod,
                    'paid_amount'           => $paid,
                    'change_amount'         => $change,
                    'payment_status'        => 'paid',
                    'note'                  => $data['note'] ?? null,
                    'created_by'            => auth()->id(),
                ]);

                $sale->update([
                    'invoice_no' => 'INV-' . now()->format('ymd') . '-' . str_pad($sale->id, 5, '0', STR_PAD_LEFT),
                ]);

                $sale->items()->createMany($itemsToSave);

                // Reduce stock
                foreach ($wanted as $foodId => $qty) {
                    $foods->get($foodId)->decrement('quantity', $qty);
                }

                return $sale;
            });
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }

        return redirect()
            ->route('admin.pos.invoice', $sale->id)
            ->with('success', 'Sale completed — Invoice ' . $sale->invoice_no);
    }

    /**
     * Printable invoice / receipt.
     */
    public function invoice($id)
    {
        $sale = PosSale::with(['items', 'cashier'])->findOrFail($id);

        return view('backend.pages.pos.invoice', compact('sale'));
    }

    /**
     * Sales history (billing list) with filters.
     */
    public function sales(Request $request)
    {
        $query = PosSale::with('cashier')->latest();

        if ($request->filled('invoice_no')) {
            $query->where('invoice_no', 'like', '%' . trim($request->invoice_no) . '%');
        }

        if ($request->filled('order_type')) {
            $query->where('order_type', $request->order_type);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59',
            ]);
        }

        $sales = $query->paginate(15)->withQueryString();

        // Summary of the (filtered) result set
        $summary = [
            'count' => (clone $query)->count(),
            'total' => (clone $query)->sum('total'),
        ];

        return view('backend.pages.pos.sales', compact('sales', 'summary'));
    }
}
