@extends('backend.master')
@section('title', 'Run #' . $run->id)

@section('content')
<div class="container-fluid">

    @php
        $orderCount = count($run->order_ids ?? []);
        $isDone     = $run->status === 'completed';
        $manName    = $run->deliveryMan->name ?? null;

        // Line items are the source of truth for what this run is worth.
        $grandTotal = $orders->sum(
            fn ($order) => $order->items->sum(fn ($item) => $item->price * $item->quantity)
        );
    @endphp

    <x-page-header
        title="Run #{{ $run->id }}"
        subtitle="{{ $orderCount }} {{ $orderCount === 1 ? 'order' : 'orders' }} carried by {{ $manName ?? 'an unassigned rider' }}."
        icon="feather-map"
        :breadcrumb="['Delivery' => null, 'Delivery Runs' => route('admin.delivery-runs.index'), 'Run #' . $run->id => null]">

        <a href="{{ route('admin.delivery-runs.index') }}" class="btn btn-soft">
            <i class="feather-arrow-left"></i> Back to Delivery Runs
        </a>

        @can('delivery_runs.edit')
            <a href="{{ route('admin.delivery-runs.edit', $run->id) }}" class="btn btn-soft-warning">
                <i class="feather-edit-2"></i> Edit
            </a>

            @if (! $isDone)
                <form action="{{ route('admin.delivery-runs.complete', $run->id) }}"
                      method="POST" class="delete-form"
                      data-confirm-title="Mark run #{{ $run->id }} as delivered?"
                      data-confirm-text="All {{ $orderCount }} order(s) on this run will be set to delivered."
                      data-confirm-button="Yes, complete it">
                    @csrf
                    @method('PATCH')

                    <button type="submit" class="btn btn-primary">
                        <i class="feather-check-circle"></i> Complete Run
                    </button>
                </form>
            @endif
        @endcan
    </x-page-header>

    <div class="row g-3">

        {{-- ================= SUMMARY ================= --}}
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header"><h5>Run Summary</h5></div>

                <div class="card-body">
                    <div class="kv-list">
                        <div class="kv-row">
                            <span class="kv-key">Delivery Man</span>
                            <span class="kv-val">{{ $manName ?? 'Unassigned' }}</span>
                        </div>

                        <div class="kv-row">
                            <span class="kv-key">Phone</span>
                            <span class="kv-val">{{ $run->deliveryMan->phone ?? '—' }}</span>
                        </div>

                        <div class="kv-row">
                            <span class="kv-key">Status</span>
                            <span class="kv-val">
                                <span class="badge {{ $isDone ? 'bg-success' : 'bg-warning' }}">
                                    {{ $isDone ? 'Completed' : 'On The Way' }}
                                </span>
                            </span>
                        </div>

                        <div class="kv-row">
                            <span class="kv-key">Orders</span>
                            <span class="kv-val num">{{ $orderCount }}</span>
                        </div>

                        <div class="kv-row">
                            <span class="kv-key">Departed</span>
                            <span class="kv-val">
                                {{ $run->departed_at ? $run->departed_at->format('d M Y, h:i A') : '—' }}
                            </span>
                        </div>

                        <div class="kv-row">
                            <span class="kv-key">Delivered</span>
                            <span class="kv-val">
                                {{ $run->returned_at ? $run->returned_at->format('d M Y, h:i A') : 'Not yet' }}
                            </span>
                        </div>

                        <hr class="hr-soft" style="margin:2px 0">

                        <div class="kv-row">
                            <span class="kv-key">Run Value</span>
                            <span class="kv-val num" style="font-size:16px">
                                BDT {{ number_format($grandTotal, 2) }}
                            </span>
                        </div>
                    </div>

                    @if ($run->note)
                        <hr class="hr-soft">
                        <p class="section-title">Note for the rider</p>
                        <p class="text-muted fs-13 mb-0">{{ $run->note }}</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- ================= ORDERS ================= --}}
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5>Orders on This Run</h5>
                    <span class="text-muted fs-13">Open a row to see the items</span>
                </div>

                <div class="table-scroll">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Delivery Address</th>
                                <th class="num">Items</th>
                                <th class="num">Total (BDT)</th>
                                <th class="text-end">Details</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($orders as $order)
                                @php
                                    $orderTotal = $order->items->sum(fn ($item) => $item->price * $item->quantity);
                                @endphp

                                <tr>
                                    <td>
                                        @can('orders.view')
                                            <a href="{{ route('admin.orders.show', $order->id) }}" class="cell-title">
                                                {{ $order->order_number }}
                                            </a>
                                        @else
                                            <p class="cell-title">{{ $order->order_number }}</p>
                                        @endcan
                                        <p class="cell-sub">
                                            {{ ucfirst(str_replace('_', ' ', (string) $order->order_status)) }}
                                        </p>
                                    </td>

                                    <td>
                                        <p class="cell-title">{{ $order->name }}</p>
                                        <p class="cell-sub">{{ $order->phone }}</p>
                                    </td>

                                    <td class="text-muted fs-13">{{ $order->address }}</td>

                                    <td class="num">{{ $order->items->count() }}</td>

                                    <td class="num fw-650">{{ number_format($orderTotal, 2) }}</td>

                                    <td class="text-end">
                                        <div class="action-group justify-content-end">
                                            <button type="button" class="btn btn-icon btn-soft-primary"
                                                    data-ar-toggle="#items-{{ $order->id }}"
                                                    title="Show items">
                                                <i class="feather-list"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr id="items-{{ $order->id }}" hidden>
                                    <td colspan="6" data-label="" style="background:var(--ar-surface-2)">
                                        <p class="section-title">Items in {{ $order->order_number }}</p>

                                        <div class="kv-list">
                                            @forelse ($order->items as $item)
                                                <div class="kv-row">
                                                    <span class="kv-key">
                                                        {{ $item->food->name ?? 'Item no longer on the menu' }}
                                                        &middot; {{ $item->quantity }} ×
                                                        {{ number_format((float) $item->price, 2) }}
                                                    </span>
                                                    <span class="kv-val num">
                                                        {{ number_format($item->price * $item->quantity, 2) }}
                                                    </span>
                                                </div>
                                            @empty
                                                <div class="kv-row">
                                                    <span class="kv-key">No line items were recorded</span>
                                                    <span class="kv-val num">0.00</span>
                                                </div>
                                            @endforelse

                                            <hr class="hr-soft" style="margin:2px 0">

                                            <div class="kv-row">
                                                <span class="kv-key">Customer Total</span>
                                                <span class="kv-val num">
                                                    BDT {{ number_format($orderTotal, 2) }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" data-label="">
                                        <x-empty-state icon="feather-inbox"
                                                       title="No orders are attached to this run"
                                                       message="The orders may have been removed after the run was created." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="table-foot">
                    <p class="foot-note">
                        {{ $orders->count() }} {{ $orders->count() === 1 ? 'order' : 'orders' }} shown
                    </p>
                    <p class="mb-0 fw-650">
                        Grand Total: BDT {{ number_format($grandTotal, 2) }}
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
