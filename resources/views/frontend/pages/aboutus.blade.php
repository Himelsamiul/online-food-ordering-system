@extends('frontend.master')

@section('title', 'About Us')

@push('styles')
<style>
    .about-page .sf-page-head { margin-bottom: 30px; }

    .about-page .sf-page-head h2 {
        color: var(--sf-ink);
        font-weight: 700;
        font-size: 2.4rem;
        margin-bottom: 8px;
    }

    .about-page .sf-page-head p {
        color: var(--sf-muted);
        max-width: 640px;
        margin: 0 auto;
        line-height: 1.75;
    }

    .about-page .sf-page-head.text-left p { margin-left: 0; }

    .sf-eyebrow {
        display: inline-block;
        margin-bottom: 8px;
        color: var(--sf-accent);
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .about-block { margin-bottom: 56px; }

    /* ---------- story ---------- */
    .story-panel { padding: 34px; }

    .story-img {
        border-radius: var(--sf-radius);
        overflow: hidden;
        background: rgba(0, 0, 0, .35);
    }

    .story-img img {
        width: 100%;
        display: block;
    }

    .story-panel .detail-box p {
        color: var(--sf-muted);
        line-height: 1.8;
        margin-bottom: 16px;
    }

    .story-signature {
        margin-top: 22px;
        padding-top: 18px;
        border-top: 1px solid var(--sf-glass-line);
        color: var(--sf-faint);
        font-size: 13.5px;
    }

    .story-signature strong {
        display: block;
        color: var(--sf-ink);
        font-family: 'Dancing Script', cursive;
        font-size: 26px;
        line-height: 1.2;
        font-weight: 700;
    }

    /* ---------- value propositions ---------- */
    .value-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 18px;
    }

    .value-card {
        padding: 26px 24px;
        transition: transform .22s ease, border-color .22s ease;
    }

    .value-card:hover {
        transform: translateY(-5px);
        border-color: var(--sf-accent) !important;
    }

    .value-icon {
        width: 52px;
        height: 52px;
        margin-bottom: 16px;
        border-radius: 17px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: rgba(0, 0, 0, .85);
        background: linear-gradient(135deg, var(--sf-accent), var(--sf-accent-dark));
        box-shadow: 0 12px 26px rgba(0, 0, 0, .4);
    }

    .value-card h6 {
        color: var(--sf-ink);
        font-weight: 700;
        font-size: 16px;
        margin-bottom: 7px;
    }

    .value-card p {
        color: var(--sf-muted);
        font-size: 14px;
        line-height: 1.65;
        margin-bottom: 0;
    }

    /* ---------- a day in the kitchen ---------- */
    .day-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 18px;
    }

    .day-card { padding: 26px 24px; }

    .day-time {
        display: inline-block;
        margin-bottom: 12px;
        padding: 5px 14px;
        border-radius: 20px;
        background: rgba(255, 255, 255, .08);
        border: 1px solid var(--sf-glass-line);
        color: var(--sf-accent);
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
    }

    .day-card h6 {
        color: var(--sf-ink);
        font-weight: 700;
        font-size: 16px;
        margin-bottom: 7px;
    }

    .day-card p {
        color: var(--sf-muted);
        font-size: 14px;
        line-height: 1.65;
        margin-bottom: 0;
    }

    /* ---------- promises list ---------- */
    .promise-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .promise-list li {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 11px 0;
        color: rgba(255, 255, 255, .82);
        font-size: 14.5px;
        line-height: 1.6;
        border-bottom: 1px solid rgba(255, 255, 255, .08);
    }

    .promise-list li:last-child { border-bottom: none; }
    .promise-list i { color: var(--sf-green); margin-top: 4px; }

    /* ---------- closing call to action ---------- */
    .cta-band {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 22px;
        padding: 34px 36px;
    }

    .cta-band h3 {
        color: var(--sf-ink);
        font-weight: 700;
        margin-bottom: 6px;
    }

    .cta-band p {
        color: var(--sf-muted);
        margin-bottom: 0;
        max-width: 520px;
    }

    .cta-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .view-menu-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 14px 34px;
        border-radius: 30px;
        background: linear-gradient(135deg, var(--sf-accent), var(--sf-accent-dark));
        color: rgba(0, 0, 0, .85);
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 14px 30px rgba(0, 0, 0, .45);
        transition: transform .18s;
    }

    .view-menu-btn:hover,
    .view-menu-btn:focus {
        transform: translateY(-2px);
        color: rgba(0, 0, 0, .85);
        text-decoration: none;
    }

    .cta-ghost {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 14px 30px;
        border-radius: 30px;
        background: rgba(255, 255, 255, .1);
        border: 1px solid var(--sf-glass-line);
        color: var(--sf-ink);
        font-weight: 700;
        text-decoration: none;
        transition: background-color .18s, border-color .18s;
    }

    .cta-ghost:hover,
    .cta-ghost:focus {
        background: rgba(255, 255, 255, .18);
        border-color: var(--sf-accent);
        color: var(--sf-ink);
        text-decoration: none;
    }

    @media (max-width: 767.98px) {
        .about-block { margin-bottom: 40px; }
        .about-page .sf-page-head h2 { font-size: 1.95rem; }
        .story-panel .img-box { margin-bottom: 26px; }
        .cta-band { padding: 26px 22px; }
    }
</style>
@endpush

@section('content')

{{-- The template's own .about_section paints a flat slate plate; this page
     keeps its own class so the dark backdrop shows through the glass panels. --}}
<section class="about-page-section about-page layout_padding">
    <div class="container">

        {{-- ================= INTRO ================= --}}
        <div class="sf-page-head text-center">
            <span class="sf-eyebrow">Who we are</span>
            <h2>We are Feane</h2>
            <p>
                One small kitchen, a short menu, and a simple rule — nothing leaves the pass
                until we would happily eat it ourselves.
            </p>
        </div>

        {{-- ================= STORY ================= --}}
        <div class="about-block">
            <div class="story-panel glass-card">
                <div class="row align-items-center">

                    <div class="col-lg-6">
                        <div class="img-box story-img">
                            <img src="{{ asset('images/about-img.png') }}" alt="Inside the Feane kitchen">
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="detail-box">
                            <span class="sf-eyebrow">Our story</span>

                            <p>
                                Feane started as a two-burner takeaway counter with a chalkboard menu
                                and a queue that spilled onto the street. We never grew the menu much.
                                We grew the kitchen instead.
                            </p>

                            <p>
                                Today the counter is a full kitchen and the chalkboard is this website,
                                but the way we cook has not changed. Produce is bought in the morning,
                                prepped by hand, and cooked once you order — never held under a lamp.
                            </p>

                            <p>
                                Order online, pay by card or cash on delivery, and follow your order
                                from the pan to your door.
                            </p>

                            <div class="story-signature">
                                <strong>The Feane kitchen</strong>
                                Cooking in Dhaka since day one
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- ================= VALUES ================= --}}
        <div class="about-block">
            <div class="sf-page-head text-center">
                <span class="sf-eyebrow">What you get</span>
                <h2>Why order from us</h2>
                <p>Four things we hold ourselves to on every single order.</p>
            </div>

            <div class="value-row">
                <div class="value-card glass-card">
                    <div class="value-icon">
                        <i class="fa fa-cutlery" aria-hidden="true"></i>
                    </div>
                    <h6>Cooked to order</h6>
                    <p>
                        Nothing is pre-plated. Your dish goes on the heat when the order lands,
                        so it reaches you the way it left the pan.
                    </p>
                </div>

                <div class="value-card glass-card">
                    <div class="value-icon">
                        <i class="fa fa-leaf" aria-hidden="true"></i>
                    </div>
                    <h6>Bought fresh, daily</h6>
                    <p>
                        Vegetables, meat and fish come in each morning from suppliers we have
                        used for years, and whatever runs out simply comes off the menu.
                    </p>
                </div>

                <div class="value-card glass-card">
                    <div class="value-icon">
                        <i class="fa fa-tags" aria-hidden="true"></i>
                    </div>
                    <h6>Honest prices</h6>
                    <p>
                        The price on the menu is the price in your cart. Discounts and coupon
                        codes are shown as a clear amount before you pay.
                    </p>
                </div>

                <div class="value-card glass-card">
                    <div class="value-icon">
                        <i class="fa fa-truck" aria-hidden="true"></i>
                    </div>
                    <h6>Delivered hot</h6>
                    <p>
                        Packed in insulated boxes and handed to a rider straight away, with a
                        status you can follow from your profile.
                    </p>
                </div>
            </div>
        </div>

        {{-- ================= A DAY IN THE KITCHEN ================= --}}
        <div class="about-block">
            <div class="sf-page-head text-center">
                <span class="sf-eyebrow">Behind the pass</span>
                <h2>A day in our kitchen</h2>
                <p>The same three shifts, every day of the week.</p>
            </div>

            <div class="day-row">
                <div class="day-card glass-card">
                    <span class="day-time">06:00 — Market</span>
                    <h6>We shop before we cook</h6>
                    <p>
                        The day starts at the market, not in the store cupboard. What looks best
                        that morning decides what we put out that evening.
                    </p>
                </div>

                <div class="day-card glass-card">
                    <span class="day-time">09:00 — Prep</span>
                    <h6>Everything by hand</h6>
                    <p>
                        Stocks on, spices ground, marinades resting. Three hours of quiet work so
                        that service can be quick without cutting corners.
                    </p>
                </div>

                <div class="day-card glass-card">
                    <span class="day-time">10:00 — Service</span>
                    <h6>Open until ten at night</h6>
                    <p>
                        Orders come off the site straight onto the pass. Twelve hours later we
                        clean down and start again tomorrow.
                    </p>
                </div>
            </div>
        </div>

        {{-- ================= PROMISES ================= --}}
        <div class="about-block">
            <div class="row align-items-center">

                <div class="col-lg-6 mb-4">
                    <div class="sf-page-head text-left">
                        <span class="sf-eyebrow">No small print</span>
                        <h2>What we promise</h2>
                        <p>
                            Ordering food online should be boring in the best way — you know what
                            you are paying, and you know when it arrives.
                        </p>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="glass-card p-4">
                        <ul class="promise-list">
                            <li>
                                <i class="fa fa-check-circle" aria-hidden="true"></i>
                                <span>Every dish on the menu is in stock — sold-out items are marked, not hidden.</span>
                            </li>
                            <li>
                                <i class="fa fa-check-circle" aria-hidden="true"></i>
                                <span>Discounts are shown as a real amount in taka, on the dish and in the cart.</span>
                            </li>
                            <li>
                                <i class="fa fa-check-circle" aria-hidden="true"></i>
                                <span>Pay by card at checkout or in cash when the rider hands it over.</span>
                            </li>
                            <li>
                                <i class="fa fa-check-circle" aria-hidden="true"></i>
                                <span>Something wrong with an order? Message us and we will make it right.</span>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>

        {{-- ================= CLOSING CTA ================= --}}
        <div class="cta-band glass-card">
            <div>
                <h3>Hungry now?</h3>
                <p>
                    The full menu is one click away — filter it by category, price, or whatever
                    you are craving tonight.
                </p>
            </div>

            <div class="cta-actions">
                <a href="{{ route('menu.index') }}" class="view-menu-btn">
                    Browse the menu <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
                <a href="{{ route('contact.page') }}" class="cta-ghost">Talk to us</a>
            </div>
        </div>

    </div>
</section>

@endsection
