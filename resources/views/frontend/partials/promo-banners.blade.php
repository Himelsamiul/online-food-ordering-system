{{-- Admin-managed promo banners. Expects $promotions (with coupon). --}}
@if ($promotions->count())
<style>
    .promo-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(330px, 1fr));
        gap: 20px;
    }

    .promo-card {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        min-height: 190px;
        display: flex;
        align-items: flex-end;
        background: linear-gradient(135deg, #2b1d00, #0f0f0f);
        border: 1px solid rgba(241,184,22,.3);
        box-shadow: 0 14px 34px rgba(0,0,0,.45);
    }

    .promo-card-bg {
        position: absolute;
        inset: 0;
    }

    .promo-card-bg img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: .45;
    }

    .promo-card-inner {
        position: relative;
        z-index: 2;
        padding: 22px 24px;
        width: 100%;
        background: linear-gradient(to top, rgba(0,0,0,.9), rgba(0,0,0,.25));
        color: #fff;
    }

    .promo-card h5 {
        font-weight: 800;
        margin-bottom: 4px;
        color: #fff;
    }

    .promo-card p {
        color: rgba(255,255,255,.75);
        font-size: 14px;
        margin-bottom: 12px;
    }

    .promo-offer {
        display: inline-block;
        background: #198754;
        color: #fff;
        font-weight: 800;
        font-size: 13px;
        padding: 4px 14px;
        border-radius: 20px;
        margin-bottom: 10px;
    }

    .promo-code-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: transparent;
        border: 2px dashed #f1b816;
        color: #f1b816;
        font-family: monospace;
        font-weight: 800;
        letter-spacing: 1.5px;
        font-size: 16px;
        border-radius: 10px;
        padding: 8px 18px;
        cursor: pointer;
        transition: .2s;
    }

    .promo-code-btn:hover {
        background: #f1b816;
        color: #1c1c1c;
    }

    .promo-terms {
        color: rgba(255,255,255,.6);
        font-size: 12px;
        margin-top: 8px;
    }

    .promo-link {
        display: inline-block;
        margin-left: 10px;
        color: #f1b816;
        font-weight: 700;
        font-size: 14px;
        text-decoration: none;
    }

    .promo-link:hover { text-decoration: underline; color: #f1b816; }
</style>

<div class="promo-row">
    @foreach ($promotions as $promotion)
        @php $coupon = $promotion->coupon; @endphp

        <div class="promo-card">
            @if ($promotion->image)
                <div class="promo-card-bg">
                    <img src="{{ asset('storage/'.$promotion->image) }}"
                         alt="{{ $promotion->title }}" loading="lazy">
                </div>
            @endif

            <div class="promo-card-inner">
                @if ($coupon)
                    <span class="promo-offer">{{ $coupon->offer_label }}</span>
                @endif

                <h5>{{ $promotion->title }}</h5>

                @if ($promotion->subtitle)
                    <p>{{ $promotion->subtitle }}</p>
                @endif

                @if ($coupon)
                    <button type="button" class="promo-code-btn js-copy-code"
                            data-code="{{ $coupon->code }}">
                        <i class="fa fa-clone"></i> {{ $coupon->code }}
                    </button>

                    @if ($promotion->link_url)
                        <a href="{{ $promotion->link_url }}" class="promo-link">Shop now →</a>
                    @endif

                    <div class="promo-terms">
                        @if ($coupon->min_order_amount)
                            Min order ৳{{ number_format($coupon->min_order_amount, 0) }}
                        @else
                            No minimum order
                        @endif
                        @if ($coupon->type === 'percent' && $coupon->max_discount)
                            · up to ৳{{ number_format($coupon->max_discount, 0) }}
                        @endif
                        @if ($coupon->expires_at)
                            · valid till {{ $coupon->expires_at->format('d M Y') }}
                        @endif
                    </div>
                @elseif ($promotion->link_url)
                    <a href="{{ $promotion->link_url }}" class="promo-link">Learn more →</a>
                @endif
            </div>
        </div>
    @endforeach
</div>

@push('scripts')
<script>
$(function () {
    $('.js-copy-code').on('click', function () {
        const code = $(this).data('code');

        // navigator.clipboard needs HTTPS or localhost — fall back to the
        // old execCommand trick so this still works over plain http on LAN.
        const done = () => Swal.fire({
            icon: 'success',
            title: 'Code copied',
            text: code + ' — paste it in your cart to get the discount.',
            timer: 1800,
            showConfirmButton: false,
        });

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
@endif
