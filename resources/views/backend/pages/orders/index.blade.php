@extends('backend.master')
@section('title', 'Orders')

@section('content')
<div class="container-fluid">

    @php
        /**
         * Status vocabulary for the whole page. The list the *form* offers is
         * deliberately shorter than the list we can *render*: OrderController
         * only accepts pending/cooking/cancelled, because out_for_delivery and
         * delivered are owned by the delivery run, not by this screen.
         */
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
    @endphp

    <x-page-header
        title="Orders"
        subtitle="Every order placed from the storefront, with its payment and kitchen status."
        icon="feather-package"
        :breadcrumb="['Sales' => null, 'Orders' => null]">

        @can('orders.view')
            <a href="{{ route('admin.orders.export', request()->query()) }}" class="btn btn-soft-success">
                <i class="feather-download"></i> Export Excel
            </a>
        @endcan
    </x-page-header>

    {{-- ================= FILTER ================= --}}
    <form method="GET" action="{{ route('admin.orders.index') }}" class="filter-card">
        <div class="row g-3 align-items-end">

            <div class="col-md-3">
                <label class="filter-label" for="filter_order_number">Order No</label>
                <input type="text" id="filter_order_number" name="order_number"
                       value="{{ request('order_number') }}"
                       class="form-control" placeholder="ORD-000000-0000">
            </div>

            <div class="col-md-3">
                <label class="filter-label" for="filter_customer_name">Customer</label>
                <select name="customer_name" id="filter_customer_name" class="form-select">
                    <option value="">All Customers</option>
                    @foreach ($customers as $c)
                        <option value="{{ $c->name }}" @selected(request('customer_name') == $c->name)>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="filter-label" for="filter_phone">Phone</label>
                <input type="text" id="filter_phone" name="phone"
                       value="{{ request('phone') }}"
                       class="form-control" placeholder="01XXXXXXXXX">
            </div>

            <div class="col-md-2">
                <label class="filter-label" for="filter_payment_status">Payment Status</label>
                <select name="payment_status" id="filter_payment_status" class="form-select">
                    <option value="">All</option>
                    <option value="paid" @selected(request('payment_status') == 'paid')>Paid</option>
                    <option value="pending" @selected(request('payment_status') == 'pending')>Pending</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="filter-label" for="filter_order_status">Order Status</label>
                <select name="order_status" id="filter_order_status" class="form-select">
                    <option value="">All</option>
                    @foreach ($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected(request('order_status') == $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="filter-label" for="filter_delivery_man">Delivery Man</label>
                <select name="delivery_man_id" id="filter_delivery_man" class="form-select">
                    <option value="">All Delivery Men</option>
                    @foreach ($deliveryMen as $man)
                        <option value="{{ $man->id }}" @selected(request('delivery_man_id') == $man->id)>
                            {{ $man->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="filter-label" for="from_date">From</label>
                <input type="date" id="from_date" name="from_date"
                       value="{{ request('from_date') }}"
                       max="{{ date('Y-m-d') }}"
                       class="form-control">
            </div>

            <div class="col-md-2">
                <label class="filter-label" for="to_date">To</label>
                <input type="date" id="to_date" name="to_date"
                       value="{{ request('to_date') }}"
                       max="{{ date('Y-m-d') }}"
                       class="form-control">
            </div>

            <div class="col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="feather-filter"></i> Filter
                </button>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-soft">Reset</a>
            </div>

        </div>
    </form>

    {{-- ================= ORDERS ================= --}}
    <div class="card">
        <div class="card-header">
            <h5>All Orders</h5>
            <span class="text-muted fs-13">
                {{ $orders->total() }} {{ $orders->total() === 1 ? 'order' : 'orders' }}
                &middot; both date fields are needed for the date filter to apply
            </span>
        </div>

        <div class="table-scroll">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Delivery</th>
                        <th class="num">Total (BDT)</th>
                        <th>Payment</th>
                        <th>Order Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($orders as $order)

                        @php
                            $parts    = preg_split('/\s+/', trim((string) $order->name)) ?: [];
                            $initials = mb_strtoupper(
                                mb_substr($parts[0] ?? '', 0, 1) . mb_substr($parts[1] ?? '', 0, 1)
                            );

                            if ($initials === '') {
                                $initials = '?';
                            }

                            $statusLabel = $statusLabels[$order->order_status]
                                ?? ucfirst(str_replace('_', ' ', (string) $order->order_status));
                            $statusTone  = $statusTones[$order->order_status] ?? 'bg-secondary';

                            $deliveryMan = $order->deliveryRun?->deliveryMan?->name;

                            /*
                             * updateStatus() refuses an order that is already on a
                             * delivery run or already delivered, so the control is
                             * not offered in those cases either.
                             */
                            $statusLocked = $order->delivery_run_id !== null
                                || $order->order_status === 'delivered'
                                || !in_array($order->order_status, $editableStatuses, true);

                            $lockReason = $order->delivery_run_id !== null
                                ? 'Assigned to a delivery run — the status is managed there'
                                : ($order->order_status === 'delivered'
                                    ? 'Delivered orders can no longer be changed'
                                    : 'This status cannot be changed from here');

                            $canMarkPaid = $order->payment_method === 'cod'
                                && $order->payment_status !== 'paid';
                        @endphp

                        <tr>
                            <td>
                                <div class="cell-media">
                                    <div>
                                        <p class="cell-title">
                                            <a href="{{ route('admin.orders.show', $order->id) }}"
                                               class="text-ink" style="text-decoration:none">
                                                {{ $order->order_number }}
                                            </a>
                                        </p>
                                        <p class="cell-sub">
                                            {{ $order->created_at ? $order->created_at->format('d M Y, h:i A') : '—' }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="cell-media">
                                    <span class="avatar-initials sm">{{ $initials }}</span>
                                    <div>
                                        <p class="cell-title">{{ $order->name }}</p>
                                        <p class="cell-sub">{{ $order->phone }}</p>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="cell-media">
                                    <div>
                                        @if ($deliveryMan)
                                            <p class="cell-title">{{ $deliveryMan }}</p>
                                        @else
                                            <p class="cell-title text-muted">Not assigned</p>
                                        @endif

                                        <p class="cell-sub" title="{{ $order->address }}">
                                            {{ \Illuminate\Support\Str::limit($order->address, 42) }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="num">
                                {{ number_format((float) $order->total_amount, 2) }}

                                @if ((float) $order->discount_amount > 0)
                                    <p class="text-muted mb-0" style="font-size:12.5px">
                                        &minus;{{ number_format((float) $order->discount_amount, 2) }}
                                        @if ($order->coupon_code)
                                            &middot; {{ $order->coupon_code }}
                                        @endif
                                    </p>
                                @endif
                            </td>

                            <td>
                                <span class="badge {{ $order->payment_method === 'cod' ? 'bg-secondary' : 'bg-info' }}">
                                    {{ strtoupper($order->payment_method) }}
                                </span>

                                <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success' : 'bg-warning' }}">
                                    {{ $order->payment_status === 'paid' ? 'Paid' : 'Pending' }}
                                </span>

                                <p class="text-muted mb-0" style="font-size:12.5px"
                                   title="{{ $order->transaction_number }}">
                                    {{ $order->transaction_number
                                        ? \Illuminate\Support\Str::limit($order->transaction_number, 22)
                                        : 'No reference yet' }}
                                </p>
                            </td>

                            {{--
                                This badge is also the read-only fallback: an admin
                                without orders.edit gets no select anywhere on the row,
                                so the status is still legible to them here.
                            --}}
                            <td>
                                <span class="badge {{ $statusTone }}">{{ $statusLabel }}</span>
                            </td>

                            <td>
                                <div class="action-group">
                                    <a href="{{ route('admin.orders.show', $order->id) }}"
                                       class="btn btn-icon btn-soft-info" title="View order">
                                        <i class="feather-eye"></i>
                                    </a>

                                    @can('orders.edit')
                                        @if ($statusLocked)
                                            <span class="btn btn-icon btn-soft" title="{{ $lockReason }}">
                                                <i class="feather-lock"></i>
                                            </span>
                                        @else
                                            <form action="{{ route('admin.orders.status', $order->id) }}"
                                                  method="POST" class="action-group">
                                                @csrf
                                                @method('PATCH')

                                                <select name="order_status" class="form-select form-select-sm"
                                                        style="min-width:132px"
                                                        aria-label="Order status for {{ $order->order_number }}">
                                                    @foreach ($editableStatuses as $value)
                                                        <option value="{{ $value }}"
                                                            @selected($order->order_status === $value)>
                                                            {{ $statusLabels[$value] }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <button type="submit" class="btn btn-icon btn-soft-primary"
                                                        title="Save order status">
                                                    <i class="feather-check"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if ($canMarkPaid)
                                            <form action="{{ route('admin.orders.payment.paid', $order->id) }}"
                                                  method="POST" class="delete-form"
                                                  data-confirm-title="Cash received?"
                                                  data-confirm-text="Order {{ $order->order_number }} will be marked as paid."
                                                  data-confirm-button="Yes, mark as paid">
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit" class="btn btn-icon btn-soft-success"
                                                        title="Mark COD payment as paid">
                                                    <i class="feather-dollar-sign"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state icon="feather-package"
                                               title="No orders match this filter"
                                               message="Clear the filter to see every order placed so far.">
                                    <a href="{{ route('admin.orders.index') }}" class="btn btn-soft">Reset filters</a>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($orders->hasPages())
            <div class="table-foot">
                <p class="foot-note">Showing {{ $orders->firstItem() }}&ndash;{{ $orders->lastItem() }}
                    of {{ $orders->total() }}</p>
                {{ $orders->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

</div>
@endsection
