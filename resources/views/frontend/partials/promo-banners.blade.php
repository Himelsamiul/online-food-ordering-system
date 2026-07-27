{{-- Admin-managed promo banners. Expects $promotions (with coupon). --}}
@if ($promotions->count())
{{--
    This <style> is deliberately inline rather than in @push('styles'):
    the partial renders inside @section('content'), which the master lays
    out after it has already emitted the styles stack in <head>.
--}}
<style>
    .promo-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(330px, 1fr));
        gap: 20px;
    }

    /* .promo-card is a reveal-animation target in storefront-refresh.js —
       the class name has to stay. */
    .promo-card {
        position: relative;
        display: flex;
        align-items: flex-end;
        min-height: 218px;
        border-radius: var(--sf-radius);
        overflow: hidden;
        background: linear-gradient(135deg, rgba(255, 255, 255, .12), rgba(0, 0, 0, .55));
        border: 1px solid var(--sf-glass-line);
        box-shadow: var(--sf-shadow);
        transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
    }

    .promo-card:hover {
        transform: translateY(-4px);
        border-color: var(--sf-accent);
        box-shadow: var(--sf-shadow-lg);
    }

    .promo-card-bg {
        position: absolute;
        inset: 0;
    }

    .promo-card-bg img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: .5;
    }

    .promo-card-inner {
        position: relative;
        z-index: 2;
        padding: 22px 24px;
        width: 100%;
        /* The card sits on a photo, so this one scrim earns its keep. */
        background: linear-gradient(to top, rgba(0, 0, 0, .92) 30%, rgba(0, 0, 0, .15));
        color: var(--sf-ink);
    }

    .promo-top {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
    }

    .promo-offer {
        display: inline-block;
        background: linear-gradient(135deg, var(--sf-green-dark), var(--sf-green));
        color: var(--sf-ink);
        font-weight: 800;
        font-size: 13px;
        line-height: 1;
        padding: 6px 14px;
        border-radius: 20px;
    }

    .promo-clock {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 600;
        color: var(--sf-accent);
        background: rgba(255, 255, 255, .1);
        border: 1px solid var(--sf-glass-line);
        border-radius: 20px;
        padding: 5px 12px;
        line-height: 1;
    }

    .promo-card h5 {
        font-weight: 800;
        font-size: 21px;
        margin-bottom: 4px;
        color: var(--sf-ink);
    }

    .promo-card p {
        color: var(--sf-muted);
        font-size: 14px;
        line-height: 1.5;
        margin-bottom: 14px;
    }

    .promo-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
    }

    .promo-code-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: rgba(255, 255, 255, .06);
        border: 2px dashed var(--sf-accent);
        color: var(--sf-accent);
        font-family: monospace;
        font-weight: 800;
        letter-spacing: 1.5px;
        font-size: 15px;
        line-height: 1;
        border-radius: var(--sf-radius-sm);
        padding: 10px 16px;
        cursor: pointer;
        transition: background-color .2s, color .2s;
    }

    .promo-code-btn:hover,
    .promo-code-btn:focus {
        background: var(--sf-accent);
        color: rgba(0, 0, 0, .85);
        outline: none;
    }

    .promo-cta {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 20px;
        border-radius: 30px;
        font-size: 13.5px;
        font-weight: 700;
        line-height: 1;
        background: linear-gradient(135deg, var(--sf-accent), var(--sf-accent-dark));
        color: rgba(0, 0, 0, .85);
        text-decoration: none;
        transition: transform .18s, box-shadow .18s;
    }

    .promo-cta:hover,
    .promo-cta:focus {
        color: rgba(0, 0, 0, .85);
        text-decoration: none;
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(0, 0, 0, .5);
    }

    .promo-terms {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 12px;
    }

    .promo-terms span {
        color: var(--sf-muted);
        font-size: 11.5px;
        font-weight: 600;
        background: rgba(255, 255, 255, .07);
        border: 1px solid rgba(255, 255, 255, .1);
        border-radius: 20px;
        padding: 4px 11px;
        line-height: 1.4;
    }

    .promo-hint {
        display: block;
        margin-top: 10px;
        color: var(--sf-faint);
        font-size: 12px;
    }

    @media (max-width: 575.98px) {
        .promo-row { grid-template-columns: 1fr; }
        .promo-card-inner { padding: 18px; }
        .promo-card h5 { font-size: 19px; }
        .promo-actions > * { flex: 1 1 100%; justify-content: center; }
    }
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
                <div class="promo-top">
                    @if ($coupon)
                        <span class="promo-offer">{{ $coupon->offer_label }}</span>
                    @endif

                    @if ($coupon && $coupon->expires_at)
                        <span class="promo-clock">
                            <i class="fa fa-clock-o" aria-hidden="true"></i>
                            Ends {{ $coupon->expires_at->format('d M Y') }}
                        </span>
                    @elseif ($promotion->ends_at)
                        <span class="promo-clock">
                            <i class="fa fa-clock-o" aria-hidden="true"></i>
                            Ends {{ $promotion->ends_at->format('d M Y') }}
                        </span>
                    @endif
                </div>

                <h5>{{ $promotion->title }}</h5>

                @if ($promotion->subtitle)
                    <p>{{ $promotion->subtitle }}</p>
                @endif

                <div class="promo-actions">
                    @if ($coupon)
                        <button type="button" class="promo-code-btn js-copy-code"
                                data-code="{{ $coupon->code }}"
                                title="Copy {{ $coupon->code }}">
                            <i class="fa fa-clone" aria-hidden="true"></i> {{ $coupon->code }}
                        </button>
                    @endif

                    <a href="{{ $promotion->link_url ?: route('menu.index') }}" class="promo-cta">
                        Order now <i class="fa fa-angle-right" aria-hidden="true"></i>
                    </a>
                </div>

                @if ($coupon)
                    <div class="promo-terms">
                        <span>
                            @if ($coupon->min_order_amount)
                                Min order ৳{{ number_format($coupon->min_order_amount, 0) }}
                            @else
                                No minimum order
                            @endif
                        </span>

                        @if ($coupon->type === 'percent' && $coupon->max_discount)
                            <span>Up to ৳{{ number_format($coupon->max_discount, 0) }} off</span>
                        @endif

                        @if ($coupon->per_user_limit)
                            <span>{{ $coupon->per_user_limit }} use{{ $coupon->per_user_limit == 1 ? '' : 's' }} per customer</span>
                        @endif
                    </div>

                    <small class="promo-hint">Tap the code to copy it, then paste it in your cart.</small>
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
