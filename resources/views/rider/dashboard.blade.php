@extends('rider.layout')
@section('title', 'My drops')

@section('content')

<div class="rider-stats">
    <div class="rider-stat">
        <strong>{{ $runs->sum(fn ($run) => count($run->order_ids ?? [])) }}</strong>
        <span>On the road</span>
    </div>
    <div class="rider-stat">
        <strong>{{ $todayDelivered }}</strong>
        <span>Delivered today</span>
    </div>
</div>

@forelse ($runs as $run)
    @php
        $runOrders = collect($run->order_ids ?? [])
            ->map(fn ($id) => $orders->get($id))
            ->filter();

        $pending = $runOrders->filter(fn ($o) => $o->order_status !== 'delivered');
    @endphp

    <section class="rider-run">
        <header class="rider-run-head">
            <div>
                <strong>Run #{{ $run->id }}</strong>
                <small>
                    Started {{ optional($run->departed_at)->diffForHumans() }}
                    · {{ $pending->count() }} of {{ $runOrders->count() }} left
                </small>
            </div>

            @if ($run->note)
                <span class="rider-run-note" title="{{ $run->note }}">
                    <i class="fa fa-sticky-note-o" aria-hidden="true"></i>
                </span>
            @endif
        </header>

        @foreach ($runOrders as $order)
            @php $done = $order->order_status === 'delivered'; @endphp

            <article class="rider-order {{ $done ? 'is-done' : '' }}">
                <div class="rider-order-top">
                    <span class="rider-order-no">{{ $order->order_number }}</span>

                    @if ($done)
                        <span class="rider-badge is-done"><i class="fa fa-check" aria-hidden="true"></i> Delivered</span>
                    @elseif ($order->payment_method === 'cod' && $order->payment_status !== 'paid')
                        <span class="rider-badge is-cash">Collect ৳{{ number_format((float) $order->total_amount, 2) }}</span>
                    @else
                        <span class="rider-badge is-paid">Paid</span>
                    @endif
                </div>

                <div class="rider-order-who">
                    <strong>{{ $order->name }}</strong>
                    @if ($order->delivery_zone_name)
                        <span class="rider-zone">{{ $order->delivery_zone_name }}</span>
                    @endif
                </div>

                <p class="rider-order-address">{{ $order->address }}</p>

                <div class="rider-order-items">
                    @foreach ($order->items as $item)
                        <span>{{ $item->quantity }}× {{ $item->food?->name ?? 'Item' }}</span>
                    @endforeach
                </div>

                <div class="rider-order-actions">
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $order->phone) }}" class="rider-btn rider-btn-ghost">
                        <i class="fa fa-phone" aria-hidden="true"></i> Call
                    </a>

                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($order->address) }}"
                       target="_blank" rel="noopener" class="rider-btn rider-btn-ghost">
                        <i class="fa fa-map-marker" aria-hidden="true"></i> Map
                    </a>

                    @unless ($done)
                        <form method="POST" action="{{ route('rider.orders.delivered', $order->id) }}"
                              class="rider-confirm" data-confirm="Mark {{ $order->order_number }} as delivered?">
                            @csrf @method('PATCH')
                            <button type="submit" class="rider-btn rider-btn-primary">
                                <i class="fa fa-check" aria-hidden="true"></i> Delivered
                            </button>
                        </form>
                    @endunless
                </div>
            </article>
        @endforeach

        @if ($pending->isNotEmpty())
            <form method="POST" action="{{ route('rider.runs.complete', $run->id) }}"
                  class="rider-confirm rider-run-finish"
                  data-confirm="Mark ALL {{ $pending->count() }} remaining order(s) on run #{{ $run->id }} as delivered?">
                @csrf @method('PATCH')
                <button type="submit" class="rider-btn rider-btn-outline">
                    Finish whole run
                </button>
            </form>
        @endif
    </section>
@empty
    <div class="rider-empty">
        <i class="fa fa-coffee" aria-hidden="true"></i>
        <h3>Nothing assigned</h3>
        <p>When the office puts you on a run it will show up here.</p>
    </div>
@endforelse

@endsection

@push('scripts')
<script>
    (function () {
        // Delivered is not undoable from the phone, so it always asks first.
        document.querySelectorAll('.rider-confirm').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (!window.confirm(form.dataset.confirm)) {
                    e.preventDefault();
                    return;
                }

                var btn = form.querySelector('button[type="submit"]');
                if (btn) {
                    setTimeout(function () {
                        btn.disabled = true;
                        btn.textContent = 'Saving…';
                    }, 0);
                }
            });
        });
    })();
</script>
@endpush
