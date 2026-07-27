@extends('backend.master')
@section('title', 'Order ' . $order->order_number)

@push('styles')
<style>
    /*
        Printing an invoice: drop the admin chrome and anything interactive,
        keep the two cards. No colours are set here — the page prints in the
        light palette because the print hook below removes the dark class.
    */
    @media print {
        .nxl-header,
        .nxl-navigation,
        .page-breadcrumb,
        .page-actions,
        .no-print {
            display: none !important;
        }

        .nxl-container {
            margin-left: 0 !important;
            padding-left: 0 !important;
        }

        .nxl-content {
            padding: 0 !important;
        }

        .nxl-content .card {
            box-shadow: none !important;
            break-inside: avoid;
        }

        .nxl-content .badge,
        .nxl-content .status-pill,
        .timeline-dot {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        @page {
            size: A4;
            margin: 14mm;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    @php
        $statusLabels = [
            'pending'          => 'Pending',
            'cooking'          => 'Cooking',
            'out_for_delivery' => 'Out for Delivery',
            'delivered'        => 'Delivered',
            'cancelled'        => 'Cancelled',
        ];

        $statusTones = [
            'pending'          => 'bg-warning',
            'cooking'          => 'bg-info',
            'out_for_delivery' => 'bg-primary',
            'delivered'        => 'bg-success',
            'cancelled'        => 'bg-danger',
        ];

        $editableStatuses = ['pending', 'cooking', 'cancelled'];

        $statusLabel = $statusLabels[$order->order_status]
            ?? ucfirst(str_replace('_', ' ', (string) $order->order_status));
        $statusTone = $statusTones[$order->order_status] ?? 'bg-secondary';

        /*
         * updateStatus() rejects an order that is on a delivery run or already
         * delivered, so the control is not offered in those cases either.
         */
        $statusLocked = $order->delivery_run_id !== null
            || $order->order_status === 'delivered'
            || !in_array($order->order_status, $editableStatuses, true);

        $lockReason = $order->delivery_run_id !== null
            ? 'This order is on a delivery run — its status is managed from there.'
            : ($order->order_status === 'delivered'
                ? 'Delivered orders can no longer be changed.'
                : 'This status cannot be changed from this screen.');

        // What the line items add up to, used only when an older order row
        // predates the subtotal column and has it stored as null.
        $itemsTotal = $order->items->sum(
            fn ($item) => (float) ($item->total ?? $item->price * $item->quantity)
        );

        $subtotal = $order->subtotal !== null ? (float) $order->subtotal : $itemsTotal;
        $discount = (float) $order->discount_amount;
        $payable  = (float) $order->total_amount;

        $isPaid = $order->payment_status === 'paid';
        $paid   = $isPaid ? $payable : 0.0;
        $due    = $isPaid ? 0.0 : $payable;

        $canMarkPaid = $order->payment_method === 'cod' && !$isPaid;

        $deliveryMan = $order->deliveryRun?->deliveryMan?->name;

        // Stage list for the timeline. There is no per-stage timestamp stored,
        // so only the placed date and the last update are shown as meta.
        $flow = [
            'pending'          => ['label' => 'Order placed',     'icon' => 'feather-shopping-bag'],
            'cooking'          => ['label' => 'In the kitchen',   'icon' => 'feather-coffee'],
            'out_for_delivery' => ['label' => 'Out for delivery', 'icon' => 'feather-truck'],
            'delivered'        => ['label' => 'Delivered',        'icon' => 'feather-check-circle'],
        ];

        $isCancelled  = $order->order_status === 'cancelled';
        $reachedIndex = array_search($order->order_status, array_keys($flow), true);
        $reachedIndex = $reachedIndex === false ? 0 : $reachedIndex;
    @endphp

    <x-page-header
        title="Order {{ $order->order_number }}"
        subtitle="Placed {{ $order->created_at ? $order->created_at->format('d M Y, h:i A') : 'date unknown' }} &middot; {{ $order->items->count() }} {{ $order->items->count() === 1 ? 'item' : 'items' }}"
        icon="feather-file-text"
        :breadcrumb="['Sales' => null, 'Orders' => route('admin.orders.index'), $order->order_number => null]">

        <a href="{{ route('admin.orders.index') }}" class="btn btn-soft no-print">
            <i class="feather-arrow-left"></i> Back to Orders
        </a>

        <button type="button" onclick="window.print()" class="btn btn-primary no-print">
            <i class="feather-printer"></i> Print Invoice
        </button>
    </x-page-header>

    <div class="row g-3">

        {{-- ================= ITEMS + TOTALS ================= --}}
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5>Items</h5>
                    <span class="text-muted fs-13">
                        {{ $order->items->count() }} {{ $order->items->count() === 1 ? 'line' : 'lines' }}
                        &middot; prices are what the customer was charged
                    </span>
                </div>

                <div class="table-scroll">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="num">Unit Price (BDT)</th>
                                <th class="num">Qty</th>
                                <th class="num">Line Total (BDT)</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($order->items as $item)
                                @php
                                    $foodName = $item->food->name ?? 'Item no longer on the menu';
                                    $initials = mb_strtoupper(mb_substr(trim($foodName), 0, 2));
                                    $lineTotal = (float) ($item->total ?? $item->price * $item->quantity);
                                @endphp

                                <tr>
                                    <td>
                                        <div class="cell-media">
                                            @if ($item->food && $item->food->image)
                                                <img src="{{ asset('storage/' . $item->food->image) }}"
                                                     alt="{{ $foodName }}"
                                                     style="width:38px;height:38px;object-fit:cover;border-radius:10px">
                                            @else
                                                <span class="avatar-initials sm">{{ $initials ?: '?' }}</span>
                                            @endif

                                            <div>
                                                <p class="cell-title">{{ $foodName }}</p>
                                                <p class="cell-sub">
                                                    {{ $item->food->subcategory->category->name ?? 'Uncategorised' }}
                                                    @if ($item->food && $item->food->subcategory)
                                                        &middot; {{ $item->food->subcategory->name }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="num">{{ number_format((float) $item->price, 2) }}</td>
                                    <td class="num">{{ $item->quantity }}</td>
                                    <td class="num fw-650">{{ number_format($lineTotal, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <x-empty-state icon="feather-inbox"
                                                       title="This order has no line items"
                                                       message="Nothing was recorded against it." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer">
                    <div style="max-width:340px;margin-left:auto">
                        <div class="kv-list">
                            <div class="kv-row">
                                <span class="kv-key">Subtotal</span>
                                <span class="kv-val num">BDT {{ number_format($subtotal, 2) }}</span>
                            </div>

                            @if ($discount > 0)
                                <div class="kv-row">
                                    <span class="kv-key">
                                        Discount
                                        @if ($order->coupon_code)
                                            &middot; {{ $order->coupon_code }}
                                        @endif
                                    </span>
                                    <span class="kv-val num">&minus;BDT {{ number_format($discount, 2) }}</span>
                                </div>
                            @endif

                            <hr class="hr-soft" style="margin:2px 0">

                            <div class="kv-row">
                                <span class="kv-key">Total</span>
                                <span class="kv-val num" style="font-size:16px">BDT {{ number_format($payable, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= SIDE PANELS ================= --}}
        <div class="col-xl-4">
            <div class="row g-3">

                {{-- ---------- CUSTOMER ---------- --}}
                <div class="col-md-6 col-xl-12">
                    <div class="card h-100">
                        <div class="card-header"><h5>Customer</h5></div>
                        <div class="card-body">
                            <div class="kv-list">
                                <div class="kv-row">
                                    <span class="kv-key">Name</span>
                                    <span class="kv-val">{{ $order->name }}</span>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-key">Phone</span>
                                    <span class="kv-val">{{ $order->phone }}</span>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-key">Email</span>
                                    <span class="kv-val">{{ $order->user->email ?? '—' }}</span>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-key">Account</span>
                                    <span class="kv-val">{{ $order->user->username ?? '—' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ---------- DELIVERY ---------- --}}
                <div class="col-md-6 col-xl-12">
                    <div class="card h-100">
                        <div class="card-header"><h5>Delivery</h5></div>
                        <div class="card-body">
                            <div class="kv-list">
                                <div class="kv-row">
                                    <span class="kv-key">Address</span>
                                    <span class="kv-val">{{ $order->address }}</span>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-key">Delivery Man</span>
                                    <span class="kv-val">{{ $deliveryMan ?? 'Not assigned' }}</span>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-key">Delivery Run</span>
                                    <span class="kv-val">
                                        {{ $order->delivery_run_id ? 'Run #' . $order->delivery_run_id : '—' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ---------- PAYMENT ---------- --}}
                <div class="col-md-6 col-xl-12">
                    <div class="card h-100">
                        <div class="card-header"><h5>Payment</h5></div>
                        <div class="card-body">
                            <div class="kv-list">
                                <div class="kv-row">
                                    <span class="kv-key">Method</span>
                                    <span class="kv-val">
                                        <span class="badge {{ $order->payment_method === 'cod' ? 'bg-secondary' : 'bg-info' }}">
                                            {{ strtoupper($order->payment_method) }}
                                        </span>
                                    </span>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-key">Status</span>
                                    <span class="kv-val">
                                        <span class="badge {{ $isPaid ? 'bg-success' : 'bg-warning' }}">
                                            {{ $isPaid ? 'Paid' : 'Pending' }}
                                        </span>
                                    </span>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-key">Reference</span>
                                    <span class="kv-val">{{ $order->transaction_number ?: 'None yet' }}</span>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-key">Paid</span>
                                    <span class="kv-val num">BDT {{ number_format($paid, 2) }}</span>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-key">Due</span>
                                    <span class="kv-val num">BDT {{ number_format($due, 2) }}</span>
                                </div>
                            </div>

                            @can('orders.edit')
                                @if ($canMarkPaid)
                                    <hr class="hr-soft">

                                    <form action="{{ route('admin.orders.payment.paid', $order->id) }}"
                                          method="POST" class="delete-form no-print"
                                          data-confirm-title="Cash received?"
                                          data-confirm-text="Order {{ $order->order_number }} will be marked as paid."
                                          data-confirm-button="Yes, mark as paid">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit" class="btn btn-soft-success w-100">
                                            <i class="feather-dollar-sign"></i> Mark COD Payment as Paid
                                        </button>
                                    </form>
                                @endif
                            @endcan
                        </div>
                    </div>
                </div>

                {{-- ---------- STATUS ---------- --}}
                <div class="col-md-6 col-xl-12">
                    <div class="card h-100">
                        <div class="card-header"><h5>Order Status</h5></div>
                        <div class="card-body">

                            {{-- Read-only for everyone; the select below is the edit path. --}}
                            <div class="kv-list">
                                <div class="kv-row">
                                    <span class="kv-key">Current</span>
                                    <span class="kv-val">
                                        <span class="badge {{ $statusTone }}">{{ $statusLabel }}</span>
                                    </span>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-key">Last Updated</span>
                                    <span class="kv-val">
                                        {{ $order->updated_at ? $order->updated_at->format('d M Y, h:i A') : '—' }}
                                    </span>
                                </div>
                            </div>

                            <hr class="hr-soft">

                            <div class="timeline">
                                @if ($isCancelled)
                                    <div class="timeline-item">
                                        <span class="timeline-dot tone-success"><i class="feather-shopping-bag"></i></span>
                                        <p class="timeline-title">Order placed</p>
                                        <p class="timeline-meta">
                                            {{ $order->created_at ? $order->created_at->format('d M Y, h:i A') : '—' }}
                                        </p>
                                    </div>

                                    <div class="timeline-item">
                                        <span class="timeline-dot tone-danger"><i class="feather-x-circle"></i></span>
                                        <p class="timeline-title">Cancelled</p>
                                        <p class="timeline-meta">
                                            Updated {{ $order->updated_at ? $order->updated_at->format('d M Y, h:i A') : '—' }}
                                        </p>
                                    </div>
                                @else
                                    @foreach (array_values($flow) as $i => $stage)
                                        @php
                                            $tone = $i < $reachedIndex
                                                ? 'tone-success'
                                                : ($i === $reachedIndex ? '' : 'tone-secondary');
                                        @endphp

                                        <div class="timeline-item">
                                            <span class="timeline-dot {{ $tone }}">
                                                <i class="{{ $stage['icon'] }}"></i>
                                            </span>
                                            <p class="timeline-title">{{ $stage['label'] }}</p>
                                            <p class="timeline-meta">
                                                @if ($i === 0)
                                                    {{ $order->created_at ? $order->created_at->format('d M Y, h:i A') : '—' }}
                                                @elseif ($i === $reachedIndex)
                                                    Updated {{ $order->updated_at ? $order->updated_at->format('d M Y, h:i A') : '—' }}
                                                @elseif ($i < $reachedIndex)
                                                    Done
                                                @else
                                                    Not yet
                                                @endif
                                            </p>
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            @can('orders.edit')
                                <hr class="hr-soft">

                                @if ($statusLocked)
                                    <p class="text-muted mb-0" style="font-size:13px">
                                        <i class="feather-lock"></i> {{ $lockReason }}
                                    </p>
                                @else
                                    <form action="{{ route('admin.orders.status', $order->id) }}"
                                          method="POST" class="no-print">
                                        @csrf
                                        @method('PATCH')

                                        <label class="filter-label" for="order_status">Change status</label>
                                        <select name="order_status" id="order_status" class="form-select mb-2">
                                            @foreach ($editableStatuses as $value)
                                                <option value="{{ $value }}" @selected($order->order_status === $value)>
                                                    {{ $statusLabels[$value] }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="feather-check"></i> Update Status
                                        </button>
                                    </form>
                                @endif
                            @endcan

                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    /*
        Print in the light palette whatever the admin is using on screen —
        a dark surface prints as unreadable grey once the browser drops
        background colours. The choice itself is left untouched.
    */
    (function () {
        var DARK = 'app-skin-dark';
        var wasDark = false;

        window.addEventListener('beforeprint', function () {
            wasDark = document.documentElement.classList.contains(DARK);
            document.documentElement.classList.remove(DARK);
        });

        window.addEventListener('afterprint', function () {
            if (wasDark) {
                document.documentElement.classList.add(DARK);
            }
        });
    })();
</script>
@endpush
