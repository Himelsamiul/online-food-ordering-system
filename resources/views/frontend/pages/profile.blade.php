@extends('frontend.master')

@push('styles')
<style>
    .offer-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        border: 1px dashed rgba(241,184,22,.45);
        border-radius: 14px;
        padding: 14px 18px;
        margin-bottom: 12px;
        background: rgba(241,184,22,.06);
    }

    .offer-code {
        background: transparent;
        border: 2px dashed #f1b816;
        color: #f1b816;
        font-family: monospace;
        font-weight: 800;
        letter-spacing: 1.2px;
        border-radius: 8px;
        padding: 5px 14px;
        cursor: pointer;
        transition: .2s;
    }

    .offer-code:hover { background: #f1b816; color: #1c1c1c; }

    .offer-value {
        margin-left: 10px;
        font-weight: 800;
        color: #2ecc71;
    }

    .offer-terms {
        color: rgba(255,255,255,.65);
        font-size: 13px;
        line-height: 1.6;
    }
</style>
@endpush

@push('scripts')
<script>
$(function () {
    $('.js-copy-code').on('click', function () {
        const code = $(this).data('code');

        const done = () => Swal.fire({
            icon: 'success',
            title: 'Code copied',
            text: code + ' — paste it in your cart to get the discount.',
            timer: 1800,
            showConfirmButton: false,
        });

        // navigator.clipboard needs HTTPS or localhost; fall back for plain http
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(code).then(done);
        } else {
            const tmp = $('<textarea>').val(code).css({position:'fixed', opacity:0}).appendTo('body');
            tmp[0].select();
            document.execCommand('copy');
            tmp.remove();
            done();
        }
    });
});
</script>
@endpush

@section('content')

<section class="profile-section py-5">
    <div class="container">

        {{-- PROFILE CARD (CENTERED) --}}
        <div class="row justify-content-center mb-5">
            <div class="col-lg-5 col-md-7 col-sm-10">
                <div class="card glass-card shadow-lg p-4 text-center">

                    {{-- USER IMAGE / ICON --}}
                    <div class="mb-3">
                        @if($user->image && file_exists(public_path('storage/'.$user->image)))
                            <img src="{{ asset('storage/'.$user->image) }}"
                                 class="rounded-circle mb-2"
                                 width="130"
                                 height="130"
                                 style="object-fit: cover;">
                        @else
                            <i class="fa fa-user-circle text-secondary"
                               style="font-size:130px;"></i>
                        @endif
                    </div>

                    <h4 class="mb-0">{{ $user->full_name }}</h4>
                    <small class="text-muted">{{ '@'.$user->username }}</small>

                    <hr>

                    <p class="mb-1"><strong>Email:</strong> {{ $user->email }}</p>
                    <p class="mb-1"><strong>Phone:</strong> {{ $user->phone }}</p>
                    <p class="mb-1"><strong>Address:</strong> {{ $user->address }}</p>
                    <p class="mb-0"><strong>DOB:</strong> {{ $user->dob }}</p>

                    {{-- EDIT BUTTON --}}
                    <hr>
                    <a href="{{ route('profile.edit') }}" class="btn btn-outline-light mt-2">
                        Edit Profile
                    </a>
                </div>
            </div>
        </div>

        {{-- 🔔 ORDER CANCELLATION NOTICE --}}
        <div class="row justify-content-center mb-4">
            <div class="col-lg-10">
                <div class="order-note">
                    📞 <strong>Order Cancellation Notice:</strong><br>
                    If you wish to cancel any order, please contact our support team at
                    <strong>+880-1234-567890</strong>.
                    Eligible payments will be refunded according to our
                    <strong>order cancellation & refund policy</strong>.
                </div>
            </div>
        </div>

        {{-- MY OFFERS --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card glass-card shadow-lg p-4">

                    <h4 class="mb-3">🎟️ My Offers</h4>

                    @forelse ($offers as $offer)
                        <div class="offer-row">
                            <div>
                                <button type="button" class="offer-code js-copy-code"
                                        data-code="{{ $offer->code }}"
                                        title="Click to copy">
                                    <i class="fa fa-clone"></i> {{ $offer->code }}
                                </button>

                                <span class="offer-value">{{ $offer->offer_label }}</span>

                                @if ($offer->description)
                                    <small class="d-block text-white-50 mt-1">{{ $offer->description }}</small>
                                @endif
                            </div>

                            <div class="offer-terms text-end">
                                @if ($offer->min_order_amount)
                                    <div>Min order ৳{{ number_format($offer->min_order_amount, 0) }}</div>
                                @else
                                    <div>No minimum order</div>
                                @endif

                                @if ($offer->type === 'percent' && $offer->max_discount)
                                    <div>Up to ৳{{ number_format($offer->max_discount, 0) }}</div>
                                @endif

                                <div>
                                    {{ $offer->expires_at
                                        ? 'Valid till ' . $offer->expires_at->format('d M Y')
                                        : 'No expiry' }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-white-50 mb-0">
                            No offers available for you right now. Check back soon!
                        </p>
                    @endforelse

                </div>
            </div>
        </div>

        {{-- ORDER HISTORY (FULL WIDTH BELOW PROFILE) --}}
        <div class="row">
            <div class="col-12">
                <div class="card glass-card shadow-lg p-4">

                    <h4 class="mb-3">Order History</h4>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Order No</th>
                                    <th>Transaction No</th>
                                    <th>Customer</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                    <th>Total (৳)</th>
                                    <th>Payment</th>
                                    <th>Payment Status</th>
                                    <th>Order Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $index => $order)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $order->order_number }}</td>
                                        <td>{{ $order->transaction_number }}</td>
                                        <td>{{ $order->name }}</td>
                                        <td>{{ $order->phone }}</td>
                                        <td>{{ $order->address }}</td>
                                        <td>{{ number_format($order->total_amount, 2) }}</td>
                                        <td>{{ strtoupper($order->payment_method) }}</td>

                                        <td>
                                            <span class="badge bg-warning text-dark">
                                                {{ ucfirst($order->payment_status) }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="badge bg-warning text-dark">
                                                {{ ucfirst($order->order_status) }}
                                            </span>
                                        </td>

                                        <td>{{ $order->created_at->format('d M Y') }}</td>

                                        <td>
                                            <a href="{{ route('profile.order.view', $order->id) }}"
                                               class="btn btn-sm btn-primary">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center text-muted">
                                            You have not placed any orders yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <small class="text-muted">
                        * Order history is demo data for future implementation.
                    </small>
                </div>
            </div>
        </div>

    </div>
</section>

{{-- PAGE-ONLY STYLE --}}
<style>
.order-note{
    background: linear-gradient(
        135deg,
        rgba(93, 226, 119, 0.25),
        rgba(255,193,7,0.05)
    );
    border: 1px solid rgba(12, 171, 137, 0.45);
    border-radius: 16px;
    padding: 18px 22px;
    text-align: center;
    color: #16a43a;
    font-weight: 600;
    box-shadow: 0 8px 25px rgba(67, 215, 126, 0.25);
    animation: fadeSlide 0.8s ease;
}

.order-note:hover{
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(47, 148, 76, 0.35);
}

@keyframes fadeSlide {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

@endsection
